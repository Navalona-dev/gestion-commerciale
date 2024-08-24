<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Stock;
use Twig\Environment;
use App\Entity\Facture;
use App\Service\LogService;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Service\TCPDFService;
use App\Service\ApplicationManager;
use App\Service\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\ReglementFactureRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FactureEcheanceService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $session;
    public  $isCurrentDossier = false;
    private $application;
    private $tcpdf;
    private $twig;
    private $logger;
    private $security;
    private $reglementFactureRepository;
    private $logService;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager, 
        ApplicationManager  $applicationManager, 
        TCPDFService $tcpdf, 
        Environment $twig,
        LoggerInterface $affaireLogger, 
        Security $security,
        ReglementFactureRepository  $reglementFactureRepository,
        LogService $logService
        )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
        $this->tcpdf = $tcpdf;
        $this->twig = $twig;
        $this->logger = $affaireLogger;
        $this->security = $security;
        $this->reglementFactureRepository = $reglementFactureRepository;
        $this->logService = $logService;
    }

    public function add($affaire = null, $request = null, $folder = null, $form = null, $montant = null, $totalPayer = null, $grandTotal = null)
    {
        $facture = Facture::newFacture($affaire);
        $date = new \DateTime();

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        $numeroFacture = 1;
        $tabNumeroFacture = $this->getLastValideFacture();
        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }
        $facture->setNumero($numeroFacture);    
        $facture->setApplication($this->application);

        $facture->setEtat('encours');
        $facture->setValid(true);
        $facture->setStatut('encours');
        $facture->setDateCreation($date);
        $facture->setDate($date);
        $facture->setType("Facture");
        $products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
        $montantHt = 0;

        // Sortie du PDF sous forme de réponse HTTP
        foreach ($products as $key => $product) { 
            // Gestion stock
            $produitCategorie = $product->getProduitCategorie();
            $qtt = $product->getQtt();
          
            $factureDetail = new FactureDetail();
            $prix = 0;
            $prixVenteGros = null;
            $prixVenteDetail = null;
            $uniteVenteDetail = null;
            $uniteVenteGros = null;

            if ($product->getTypeVente() == "gros") {
                $montantHt  = $montantHt + ($qtt * $product->getPrixVenteGros());
                $prix = $product->getPrixVenteGros();
                $uniteVenteGros = $product->getUniteVenteGros();
                $prixVenteGros = $prix; 
            } else {
                $montantHt  = $montantHt + ($qtt * $product->getPrixVenteDetail());
                $prix = $product->getPrixVenteDetail();
                $uniteVenteDetail = $product->getUniteVenteDetail();
                $prixVenteDetail = $prix;
            }

            $factureDetail->setFacture($facture);
            $factureDetail->setReference($product->getReference());
            $factureDetail->setDetail($product->getProduitCategorie()->getNom());
            $factureDetail->setQtt($qtt);
            $factureDetail->setProduct($product);
            $factureDetail->setPrixUnitaire($prix);
            $factureDetail->setPrixTotal($montantHt);
        
            $factureDetail->setDescription($product->getDescription());
            $factureDetail->setUniteVenteDetail($uniteVenteDetail);
            $factureDetail->setUniteVenteGros($uniteVenteGros);
            $factureDetail->setPrixVenteDetail($prixVenteDetail);
            $factureDetail->setPrixVenteGros($prixVenteGros);

            $facture->addFactureDetail($factureDetail);

            //Log product
            $data["produit"] = $produitCategorie->getNom();
            $data["dateReception"] = null;
            $data["dateTransfert"] = null;
            $data["dateSortie"] = (new \DateTime())->format("d-m-y h:i:s");
            $data["userDoAction"] = $user->getUserIdentifier();
            $data["source"] = $this->application->getEntreprise();
            $data["destination"] = $affaire->getCompte()->getNom();
            $data["action"] = "Commande";
            $data["type"] = "Commande";
            $data["qtt"] = $qtt;
            $data["stockRestant"] = $produitCategorie->getStockRestant();
            $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
            $data["typeSource"] = "Point de vente";
            $data["typeDestination"] = "Client";
            $data["commande"] = $affaire->getNom();
            $data["commandeId"] = $affaire->getId().'-paye';
            $data["sourceId"] =  $this->application->getId();
            $data["destinationId"] = $affaire->getCompte()->getId();
            $this->logService->addLog($request, "commande", $this->application->getId(), $produitCategorie->getReference(), $data);

            $this->persist($factureDetail);
        }
        
        $facture->setFile($filename);
        $facture->setSolde($montantHt);
        $facture->setPrixHt($montantHt);   

        $formData = $form->getData();
        $reglement = $formData->getReglement();

        $facture->setReglement($reglement);
        $this->persist($facture);

        $formDataEcheance = $form->get('factureEcheances')->getData();
        $factureEcheances = $formDataEcheance;

        foreach($factureEcheances as $factureEcheance) {

            $factureEcheance->setStatus('encours');
            $factureEcheance->setDateCreation(new \DateTime());
            $factureEcheance->setFacture($facture);

            $this->persist($factureEcheance);

            $montant += $factureEcheance->getMontant();

            $totalPayer = $montant + $reglement;

        }
        
        $affaire->setPaiement('enecheance');
        $affaire->setDatePaiement($date);
        $affaire->setDevisEvol('encours');
        $affaire->setDateFacture($date);
        $affaire->setStatut("commande");
        $this->persist($affaire);
        
        $pdfContent = null;
        if($totalPayer > $grandTotal || $totalPayer < $grandTotal) {
            //erreur d'ajout de facture echeance
        } else {
            $this->update();

            // Initialize Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $pdf = new Dompdf($options);

            // Load HTML content
            $data = [];
            $data['produits'] = $products;
            $data['facture'] = $facture;
            $data['compte'] = $facture->getCompte();
            
            $html = $this->twig->render('admin/facture/facturePdf.html.twig', $data);

            // Load HTML to Dompdf
            $pdf->loadHtml($html);

            // (Optional) Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');

            // Render PDF
            $pdf->render();

            // Get PDF content
            $pdfContent = $pdf->output();

            // Save PDF to file
            $fileName = $folder . $filename;
            file_put_contents($fileName, $pdfContent);


            // Créer le log
            $this->logger->info('Facture  de commande créee', [
                'Commande' => $affaire->getNom(),
                'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                'Adresse e-mail' => $user ? $user->getUserIdentifier() : 'Pas d\'adresse e-mail',
                'ID Application' => $affaire->getApplication()->getId()
            ]);
        }

        return [$pdfContent, $facture, $totalPayer]; 
        
    }

    public function addNewFacture($factureEcheance = null, $form = null, $reglement = null, $reste = null, $montant = null, $folder = null)
    {
         // Obtenir l'utilisateur connecté
         $user = $this->security->getUser();

         $date = new \DateTime();

        $facture = $factureEcheance->getFacture();
        $affaire = $facture->getAffaire();

        if($reste == 0) {
            $facture->setEtat('regle');
            $facture->setStatut('regle');
            $affaire->setPaiement('paye');
            $affaire->setDevisEvol('gagne');
            $facture->setDate($date);
            $this->persist($affaire);

            $products = $affaire->getProducts();

            foreach($products as $key => $product) {
                 // Gestion de notification
                $produitCategorie = $product->getProduitCategorie();
                $stockMin = $produitCategorie->getStockMin();
                $stockRestant = $produitCategorie->getStockRestant();
    
                $qtt = $product->getQtt();
                $stockRestant = $stockRestant - $qtt;
                $produitCategorie->setStockRestant($stockRestant);
                
                $this->persist($produitCategorie);
    
                if ($stockRestant <= $stockMin) {
                    $notification = new Notification();
                    $message = 'Le stock du produit ' . '<strong>' . $produitCategorie->getNom() . '</strong>' . ' est presque épuisé, veuillez ajouter un ou plusieurs!!';
                    $notification->setMessage($message)
                                ->setDateCreation(new \DateTime())
                                ->setApplication($this->application)
                                ->setProduitCategorie($produitCategorie)
                                ->setStockMin(true);
                    $this->persist($notification);
                }
    
               // Récupération des stocks et tri par date de péremption (de la plus proche à la plus éloignée)
                $stocks = $this->entityManager->getRepository(Stock::class)->findByProduitCategorieDatePerremptionIsNotNull($produitCategorie);
    
                // Tri des stocks par date de péremption (de la plus proche à la plus éloignée)
                usort($stocks, function($a, $b) {
                    $dateA = $a->getDatePeremption()->getDate();
                    $dateB = $b->getDatePeremption()->getDate();
                    
                    // Comparer les dates
                    return $dateA <=> $dateB;
                });
               
                // Réduction des quantités de stock en fonction de la date de péremption la plus proche
                foreach ($stocks as $stk) {
                    $qttRestant = $stk->getQttRestant();
                    
                    if ($qtt <= 0) {
                        break; // Si la quantité à réduire est déjà consommée, on sort de la boucle
                    }
                    
                    if ($qttRestant >= $qtt) {
                        // Réduit la quantité restante du stock actuel
                        $stk->setQttRestant($qttRestant - $qtt);
                        $this->persist($stk);
                        $qtt = 0; // Toute la quantité a été réduite
                    } else {
                        // Réduit la quantité restante du stock actuel et passe au suivant
                        $qtt -= $qttRestant;
                        $stk->setQttRestant(0); // Le stock actuel est épuisé
                        $this->persist($stk);
                    }
                }
            }
        }

        $facture->setReglement($reglement);
        $this->persist($facture);

        $newFacture = Facture::newFacture($affaire);
        $numeroFacture = 1;
        $tabNumeroFacture = $this->getLastValideFacture();
        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }
        $newFacture->setNumero($numeroFacture);    
        $newFacture->setApplication($this->application);

        $newFacture->setEtat('regle');
        $newFacture->setValid(true);
        $newFacture->setStatut('regle');
        $newFacture->setDateCreation($date);
        $newFacture->setDate($date);
        $newFacture->setType("Facture");
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $newFacture->getNumero() . ".pdf";
        
        $newFacture->setFile($filename);
        $newFacture->setSolde($montant);
        $newFacture->setPrixHt($montant); 
        $newFacture->setEcheance(true);
        $this->persist($newFacture);  

        $formData = $form->getData();
        $status = $formData->getStatus();

        $factureEcheance->setStatus($status);
        $factureEcheance->setFile($filename);
        $this->persist($factureEcheance);

        $this->update();

         // Initialize Dompdf
         $options = new Options();
         $options->set('isHtml5ParserEnabled', true);
         $options->set('isPhpEnabled', true);
         $pdf = new Dompdf($options);
 
         // Load HTML content
         $data = [];
         $data['facture'] = $facture;
         $data['newFacture'] = $newFacture;
         $data['factureEcheance'] = $factureEcheance;
         $data['compte'] = $facture->getCompte();
         
         $html = $this->twig->render('admin/facture_echeance/facturePdf.html.twig', $data);
 
         // Load HTML to Dompdf
         $pdf->loadHtml($html);
 
         // (Optional) Set paper size and orientation
         $pdf->setPaper('A4', 'portrait');
 
         // Render PDF
         $pdf->render();
 
         // Get PDF content
         $pdfContent = $pdf->output();
 
         // Save PDF to file
         $fileName = $folder . $filename;
         file_put_contents($fileName, $pdfContent);
 
 
         // Créer le log
         $this->logger->info('Facture écheance payée', [
             'Commande' => $affaire->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getUserIdentifier() : 'Pas d\'adresse e-mail',
             'ID Application' => $affaire->getApplication()->getId()
         ]);
 
         return [$pdfContent, $newFacture]; 
 

    }

    public function factureReporter($factureEcheance = null, $form = null, $folder = null)
    {
        $user = $this->security->getUser();

        $facture = $factureEcheance->getFacture();
        $affaire = $facture->getAffaire();

        $date = new \DateTime();

        $formData = $form->getData();
        $avance = 0;
        if($formData->getReglement() != '') {
            $avance = $formData->getReglement();
        }
        
        $factureEcheance->setReporter(true);
        $factureEcheance->setStatus('reporter');
        $this->persist($factureEcheance);

        $facture->setReglement($facture->getReglement() + $avance);
        
        $newFacture = Facture::newFacture($affaire);
        $numeroFacture = 1;
        $tabNumeroFacture = $this->getLastValideFacture();
        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }
        $newFacture->setNumero($numeroFacture);    
        $newFacture->setApplication($this->application);

        $newFacture->setEtat('regle');
        $newFacture->setValid(true);
        $newFacture->setStatut('regle');
        $newFacture->setDateCreation($date);
        $newFacture->setDate($date);
        $newFacture->setType("Facture");
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $newFacture->getNumero() . ".pdf";
        
        $newFacture->setFile($filename);
        $newFacture->setSolde($avance);
        $newFacture->setPrixHt($avance); 
        $newFacture->setEcheance(true);
        $this->persist($newFacture);

        $montant = $factureEcheance->getMontant();
        $reglementEcheance = $factureEcheance->getReglement();
        $pdfContent = null;

        if($reglementEcheance > $montant) {
            //erreur
        } else {

        $this->update();

         // Initialize Dompdf
         $options = new Options();
         $options->set('isHtml5ParserEnabled', true);
         $options->set('isPhpEnabled', true);
         $pdf = new Dompdf($options);
 
         // Load HTML content
         $data = [];
         $data['facture'] = $facture;
         $data['newFacture'] = $newFacture;
         $data['factureEcheance'] = $factureEcheance;
         $data['compte'] = $facture->getCompte();
         
         $html = $this->twig->render('admin/facture_echeance/facturePdf.html.twig', $data);
 
         // Load HTML to Dompdf
         $pdf->loadHtml($html);
 
         // (Optional) Set paper size and orientation
         $pdf->setPaper('A4', 'portrait');
 
         // Render PDF
         $pdf->render();
 
         // Get PDF content
         $pdfContent = $pdf->output();
 
         // Save PDF to file
         $fileName = $folder . $filename;
         file_put_contents($fileName, $pdfContent);
 
         // Créer le log
         $this->logger->info('Facture écheance payée', [
             'Commande' => $affaire->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getUserIdentifier() : 'Pas d\'adresse e-mail',
             'ID Application' => $affaire->getApplication()->getId()
         ]);
        }

 
         return [$pdfContent, $newFacture, $reglementEcheance]; 
 
    }

  

    public function update()
    {
        $this->entityManager->flush();
    }

    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    public function getLastValideFacture()
    {
        return $this->entityManager->getRepository(Facture::class)->getLastValideFacture();
    }
}