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
use App\Entity\FactureEcheance;
use App\Entity\ProduitCategorie;
use App\Service\ApplicationManager;
use App\Entity\DatePeremptionProduct;
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

    public function add($affaire = null, $request = null, $folder = null, $form = null, $montant = null, $totalPayer = null, $grandTotal = null, $applicationRevendeur = null)
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
        //$numeroFacture = 75;

        //$facture->setNumero($facture->getNumero());
        //$facture->setEcheanceNumero(1);

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
        $quantitesParCategorie = [];
        $tabProduitCategorie = [];

        // Sortie du PDF sous forme de réponse HTTP
        foreach ($products as $key => $product) { 
            // Gestion stock
            $produitCategorie = $product->getProduitCategorie();
            $volumeGros = $produitCategorie->getVolumeGros();
            $stockRestant = $produitCategorie->getStockRestant();
            $qtt = $product->getQtt();
        
            $factureDetail = new FactureDetail();
            $prix = 0;
            $prixVenteGros = null;
            $prixVenteDetail = null;
            $uniteVenteDetail = null;
            $uniteVenteGros = null;

            if ($product->getTypeVente() == "gros") {
                $montantHt  = ($montantHt + ($qtt * $product->getPrixVenteGros())) - $product->getRemise();
                $prix = $product->getPrixVenteGros();
                $uniteVenteGros = $product->getUniteVenteGros();
                $prixVenteGros = $prix; 
            } else {
                $montantHt  = ($montantHt + ($qtt * $product->getPrixVenteDetail())) - $product->getRemise();
                $prix = $product->getPrixVenteDetail();
                $uniteVenteDetail = $product->getUniteVenteDetail();
                $prixVenteDetail = $prix;
            }

            if($product->getTypeVente() == "detail" && $volumeGros > 0) {
                $qtt = $qtt / $volumeGros;
            }

            $stockRestant = $stockRestant - $qtt;

            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);

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

            $this->persist($factureDetail);

            $produitCategorieId = $produitCategorie->getId();
            if (!isset($quantitesParCategorie[$produitCategorieId])) {
                $quantitesParCategorie[$produitCategorieId] = 0; // Initialiser si pas encore présent
            }
            $quantitesParCategorie[$produitCategorieId] += $qtt; // Additionner la quantité

            // Ajouter seulement si la produitCategorie n'est pas déjà dans le tableau
            $produitCategorieId = $produitCategorie->getId();
            if (!isset($tabProduitCategorie[$produitCategorieId])) {
                $tabProduitCategorie[$produitCategorieId] = $produitCategorie;
            }

            // Gestion des notifications
            $stockMin = $produitCategorie->getStockMin();
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

            $stocks = $this->entityManager->getRepository(Stock::class)->findByProductCategoryDatePeremption($produitCategorie);
            foreach ($stocks as $keyS => $stk) {
                $qttRestant = $stk->getQttRestant();

                //$qtt = number_format($qtt,2,'.','');

                $newQttRestant = $qttRestant - $qtt; 

                if ($qtt <= 0) {
                    break;
                }

                if ($qttRestant >= $qtt) {
                    $stk->setQttRestant($newQttRestant);
                    $this->persist($stk);
                    
                    $datePeremptionProduct = new DatePeremptionProduct();
                    $datePeremptionProduct->setProduct($product);
                    $datePeremptionProduct->setStock($stk);
                    if($stk->getDatePeremption() == null) {
                        $datePeremptionProduct->setDatePeremption(null);
                    } else {
                        $datePeremptionProduct->setDatePeremption($stk->getDatePeremption()->getDate());
                    }
                    $datePeremptionProduct->setQttRetenue($qtt);
                    $this->persist($datePeremptionProduct);

                    $qtt = 0;
                } else {

                    $datePeremptionProduct = new DatePeremptionProduct();
                    $datePeremptionProduct->setProduct($product);
                    $datePeremptionProduct->setStock($stk);
                    if($stk->getDatePeremption() == null) {
                        $datePeremptionProduct->setDatePeremption(null);
                    } else {
                        $datePeremptionProduct->setDatePeremption($stk->getDatePeremption()->getDate());
                    }
                    $datePeremptionProduct->setQttRetenue($qttRestant);
                    $this->persist($datePeremptionProduct);

                    $qtt -= $qttRestant;
                    $stk->setQttRestant(0);
                    $this->persist($stk);
                }

            }

            //gerer la qtt reserver
            $qttProduct = $product->getQtt();
            $qttReserverGros = $produitCategorie->getQttReserverGros();
            $qttReserverDetail = $produitCategorie->getQttReserverDetail();
            $qttReserverCommander = $produitCategorie->getQttReserverCommander();
            if($product->getTypeVente() == "gros") {
                if($produitCategorie->getQttReserverGros() != null) {
                    $produitCategorie->setQttReserverGros($qttReserverGros - $qttProduct);
                }
            }

            if($product->getTypeVente() == "detail") {
                if($produitCategorie->getQttReserverDetail() != null) {
                    $produitCategorie->setQttReserverDetail($qttReserverDetail - $qttProduct);
                }

                $qttProduct = $qttProduct / $volumeGros;
            }


            if($produitCategorie->getQttReserverCommander() != null){
                $produitCategorie->setQttReserverCommander($qttReserverCommander + $qttProduct);
            }else {
                $produitCategorie->setQttReserverCommander($qttProduct);
            }

            $this->entityManager->persist($produitCategorie);

            //gerer le qtt reserver commander par sac et unité
            //$qttReserverCommander = $produitCategorie->getQttReserverCommander();
            $qttReserverCommander = $qttProduct;
            $sacs = floor($qttReserverCommander);

            // Unité (partie décimale)
            $decimal = $qttReserverCommander - $sacs;
            $unite = $decimal * $produitCategorie->getVolumeGros();
            $messageUnite = '';
            if($unite > 0) {
                $unite = number_format($unite,2,'.','');
                $messageUnite = ' et ' . $unite . ' ' . $produitCategorie->getUniteVenteGros();
            }
            $qttReserverCommanderFinal = $sacs . ' ' . $produitCategorie->getPresentationGros() . $messageUnite;

            //gerer le stock restant par sac et unité
            $stockRestant = $produitCategorie->getStockRestant();
            $sacsStock = floor($stockRestant);

            // Unité (partie décimale)
            $decimalStock = $stockRestant - $sacsStock;
            $uniteStock = $decimalStock * $produitCategorie->getVolumeGros();
            if($uniteStock > 0) {
                $uniteStock = number_format($uniteStock,2,'.','');
                $messageUnite = ' et ' . $uniteStock . ' ' . $produitCategorie->getUniteVenteGros();
            }
            $stockRestantFinal = $sacsStock . ' ' . $produitCategorie->getPresentationGros() . $messageUnite;

            //Log product
            $data["produit"] = $produitCategorie->getNom();
            $data["dateReception"] = null;
            $data["dateTransfert"] = null;
            $data["dateSortie"] = (new \DateTime())->format("d-m-Y h:i:s");
            $data["userDoAction"] = $user->getUserIdentifier();
            $data["source"] = $this->application->getEntreprise();
            $data["destination"] = $affaire->getCompte()->getNom();
            $data["action"] = "Commande";
            $data["type"] = "Commande";
            $data["qtt"] = $qttReserverCommanderFinal;
            $data["stockRestant"] = $stockRestantFinal;
            $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
            $data["typeSource"] = "Point de vente";
            $data["typeDestination"] = "Client";
            $data["commande"] = $affaire->getNom();
            $data["commandeId"] = $affaire->getId().'-echeance';
            $data["sourceId"] =  $this->application->getId();
            $data["destinationId"] = $affaire->getCompte()->getId();
            $this->logService->addLog($request, "commande", $this->application->getId(), $produitCategorie->getReference(), $data);
   
        }

        //dd($produitCategorie->getQttReserverGros(), $produitCategorie->getQttReserverDetail(), $produitCategorie->getStockRestant());

        if($applicationRevendeur != null) {
            foreach($tabProduitCategorie as $keyProduct => $oldProduitCategorie) {
                $reference = $oldProduitCategorie->getReference();

                $existingProduitCategorie = $this->entityManager->getRepository(ProduitCategorie::class)
                ->findOneBy([
                    'reference' => $reference,
                    'application' => $applicationRevendeur,
                ]);

                 // Obtenir la quantité totale pour la catégorie
                 $produitCategorieId = $oldProduitCategorie->getId();
                 $qttNewProduitCategorie = isset($quantitesParCategorie[$produitCategorieId]) ? $quantitesParCategorie[$produitCategorieId] : 0;
                 $newProduitCategorie = null;
                if ($existingProduitCategorie) {
                    $oldQttProduitCategorie = $existingProduitCategorie->getQtt();
                    $newProduitCategorie = $existingProduitCategorie;
                    
                    $oldStockRestantProduitCategorie = $existingProduitCategorie->getStockRestant();
                    if($oldStockRestantProduitCategorie != null) {
                        $newProduitCategorie->setStockRestant($oldStockRestantProduitCategorie + $qttNewProduitCategorie);
                    } else {
                        $newProduitCategorie->setStockRestant($qttNewProduitCategorie);
                    }

                    if($oldQttProduitCategorie != null) {
                        $newProduitCategorie->setQtt($oldQttProduitCategorie + $qttNewProduitCategorie);
                    } else {
                        $newProduitCategorie->setQtt($qttNewProduitCategorie);
                    }
                    $newProduitCategorie = $existingProduitCategorie;

                } else {
                    $newProduitCategorie = new ProduitCategorie();

                    $date = new \DateTime();
        
                    $newProduitCategorie->setNom($oldProduitCategorie->getNom());

                    $newProduitCategorie->setCategorie($oldProduitCategorie->getCategorie());
                    
                    $newProduitCategorie->setType($oldProduitCategorie->getType());
        
                    $newProduitCategorie->setReference($reference);

                    $newProduitCategorie->setQtt($qttNewProduitCategorie);
                    $newProduitCategorie->setStockRestant($qttNewProduitCategorie);

                    $newProduitCategorie->setApplication($applicationRevendeur);
        
                    $newProduitCategorie->setDateCreation($date);
                    $newProduitCategorie->setDescription($oldProduitCategorie->getDescription());
                    $newProduitCategorie->setPrixHt($oldProduitCategorie->getPrixHt());
                    $newProduitCategorie->setTva($oldProduitCategorie->getTva());
                    $newProduitCategorie->setStockMin(10);
                    $newProduitCategorie->setStockMax(50);
                    $newProduitCategorie->setUniteVenteGros($oldProduitCategorie->getUniteVenteGros());
                    $newProduitCategorie->setUniteVenteDetail($oldProduitCategorie->getUniteVenteDetail());
                    $newProduitCategorie->setPrixVenteGros($oldProduitCategorie->getPrixVenteGros());
                    $newProduitCategorie->setPrixVenteDetail($oldProduitCategorie->getPrixVenteDetail());
                    $newProduitCategorie->setPrixTTC($oldProduitCategorie->getPrixTTC());
                    $newProduitCategorie->setPrixAchat($oldProduitCategorie->getPrixAchat());
                    $newProduitCategorie->setPresentationGros($oldProduitCategorie->getPresentationGros());
                    $newProduitCategorie->setPresentationDetail($oldProduitCategorie->getPresentationDetail());
                    $newProduitCategorie->setVolumeGros($oldProduitCategorie->getVolumeGros());
                    $newProduitCategorie->setVolumeDetail($oldProduitCategorie->getVolumeDetail());
        
                    foreach($oldProduitCategorie->getProductImages() as $productImage) {
                        $productImage->setProduitCategorie($newProduitCategorie);
                        $productImage->setDateCreation($date);
                        $this->entityManager->persist($productImage);
                    }

                    $this->entityManager->persist($newProduitCategorie);

                    $newTabProduitCategorie[] = $newProduitCategorie;
        
                }

               //gerer le stock

                $stock = new Stock();

                $stock->setQtt($qttNewProduitCategorie);
                $stock->setQttRestant($qttNewProduitCategorie);
                $stock->setProduitCategorie($newProduitCategorie);
                $stock->setDateCreation($date);

                $this->entityManager->persist($stock);

                // Obtenir l'utilisateur connecté
                $user = $this->security->getUser();
        
                // Créer log
                $this->logger->info('Produit catégorie ajouté', [
                    'Produit' => $newProduitCategorie->getNom(),
                    'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                    'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                    'ID Application' => $newProduitCategorie->getApplication()->getId()
                ]);

            }
        }
        //dd($montantHt);
        $facture->setFile($filename);
        $facture->setSolde($montantHt);
        $facture->setPrixHt($montantHt);   

        $formData = $form->getData();
        $reglement = $formData->getReglement();

        $facture->setReglement($reglement);
        $facture->setAvance($reglement);
        $this->persist($facture);

        $formDataEcheance = $form->get('factureEcheances')->getData();
        $factureEcheances = $formDataEcheance;

        $numeroEch = 1;
        foreach($factureEcheances as $factureEcheance) {

            $factureEcheance->setStatus('encours');
            $factureEcheance->setDateCreation(new \DateTime());
            $factureEcheance->setFacture($facture);
            //$factureEcheance->setNumero($numeroEch);

            $this->persist($factureEcheance);

            $montant += $factureEcheance->getMontant();

            $totalPayer = $montant + $reglement;

            $numeroEch++;

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
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $pdf = new Dompdf($options);

            $factureEcheanceFirst = $factureEcheances[0];


            // Load HTML content
            $data = [];
            $data['produits'] = $products;
            $data['facture'] = $facture;
            $data['compte'] = $facture->getCompte();
            $data['factureEcheances'] = $factureEcheances;
            $data['factureEcheanceFirst'] = $factureEcheanceFirst;
            $data['application'] = $this->application;
            $data['user'] = $user;
            
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
        $products = $affaire->getProducts();

        if($reste == 0) {
            $facture->setEtat('termine');
            $facture->setStatut('termine');
            $affaire->setPaiement('paye');
            $affaire->setDevisEvol('gagne');
            $facture->setDate($date);
            $this->persist($affaire);

            $products = $affaire->getProducts();
            $sumQtt = 0;
            $stocks = null;
            $tabQttRetenue = [];
            $tabDatePeremption = [];
            $tabQtt = [];
            $tabQttReserver = [];
            $tabQttRestantProduitCategorie = [];

        }

        $facture->setReglement($reglement);
        $this->persist($facture);

        $newFacture = Facture::newFacture($affaire);
        $numeroFacture = 1;
        $tabNumeroFacture = $this->getLastValideFactureEcheance($facture);
        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }

        $newFacture->setNumero($facture->getNumero());
        $newFacture->setEcheanceNumero($numeroFacture);
        $newFacture->setApplication($this->application);

        $newFacture->setEtat('regle');
        $newFacture->setValid(true);
        $newFacture->setStatut('regle');
        $newFacture->setDateCreation($date);
        $newFacture->setDate($date);
        $newFacture->setType("Facture");
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $newFacture->getNumero() . '-' . $newFacture->getEcheanceNumero() . ".pdf";
        
        $newFacture->setFile($filename);
        $newFacture->setSolde($montant);
        $newFacture->setPrixHt($montant); 
        $newFacture->setEcheance(true);

        $newFacture->setReglement($factureEcheance->getMontant());

        $this->persist($newFacture);  

        $formData = $form->getData();
        $status = $formData->getStatus();

        $factureEcheance->setNumero($numeroFacture);
        $factureEcheance->setStatus($status);
        $factureEcheance->setFile($filename);
        $this->persist($factureEcheance);

        $montantPaye = null;

        if($factureEcheance->getReglement() != null && $factureEcheance->getReglement() > $factureEcheance->getMontant()) {
            $montantPaye = $factureEcheance->getReglement() - $factureEcheance->getMontant();
            
        } elseif($factureEcheance->getReglement() != null && $factureEcheance->getMontant() > $factureEcheance->getReglement())
        {
            $montantPaye = $factureEcheance->getMontant() - $factureEcheance->getReglement();
        } elseif($factureEcheance->getReglement() == null ) {
            $montantPaye = $factureEcheance->getMontant();
        }
        //dd($numeroFacture, $factureEcheance, $newFacture);
        $this->update();

        
         // Initialize Dompdf
         $options = new Options();
         $options->set('isRemoteEnabled', true);
         $options->set('isHtml5ParserEnabled', true);
         $options->set('isPhpEnabled', true);
         $pdf = new Dompdf($options);
 
         // Load HTML content
         $data = [];
         $data['facture'] = $facture;
         $data['newFacture'] = $newFacture;
         $data['factureEcheance'] = $factureEcheance;
         $data['factureEcheances'] = $facture->getFactureEcheances();
         $data['compte'] = $facture->getCompte();
         $data['montantPaye'] = $montantPaye;
         $data['produits'] = $products;
         $data['application'] = $this->application;
         $data['user'] = $user;
         $data['echeanceNumero'] = $numeroFacture;
         
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
        $products = $affaire->getProducts();

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
        $tabNumeroFacture = $this->getLastValideFactureEcheance($facture);
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
        $newFacture->setReglement($avance); 
        $newFacture->setEcheance(true);
        $this->persist($newFacture);

        $montant = $factureEcheance->getMontant();
        $reglementEcheance = $factureEcheance->getReglement();
        $pdfContent = null;

        if($reglementEcheance > $montant) {
            //erreur
        } else {

        $montantPaye = 0;
        if($factureEcheance->getReglement() == null) {
            $montantPaye = 0;
        } else {
            $montantPaye = $factureEcheance->getReglement();
        }

        $this->update();

         // Initialize Dompdf
         $options = new Options();
         $options->set('isRemoteEnabled', true);
         $options->set('isHtml5ParserEnabled', true);
         $options->set('isPhpEnabled', true);
         $pdf = new Dompdf($options);
 
         // Load HTML content
         $data = [];
         $data['facture'] = $facture;
         $data['newFacture'] = $newFacture;
         $data['factureEcheance'] = $factureEcheance;
         $data['factureEcheances'] = $facture->getFactureEcheances();
         $data['compte'] = $facture->getCompte();
         $data['produits'] = $products;
         $data['montantPaye'] = $montantPaye;
         $data['application'] = $this->application;
         $data['user'] = $user;
         $data['echeanceNumero'] = $numeroFacture;
         
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

    public function nouveauEcheance($newFactureEcheance = null, $facture = null, $form = null)
    {
        $formData = $form->getData();
        $montantData = $formData->getMontant();
        $delaiPaiement = $formData->getDelaiPaiement();
        $datePaiement = $formData->getDateEcheance();

        $factureEcheances = $facture->getFactureEcheances();
        $factureEcheanceFirst = $facture->getFactureEcheances()[0];
        $montant = $factureEcheanceFirst->getMontant();

        $error = false;

        $isFirst = true;

        $montantHt = 0;

        $solde = $facture->getSolde();

        foreach($factureEcheances as $factureEcheance) {
            $montantHt += $factureEcheance->getMontant();
            
        }

        $reste = $solde - $montantHt;

        if($reste == 0) {
            // Exécuter la condition seulement pour le premier élément
            if($factureEcheanceFirst) {
                if($montantData < $montant) {
                    $factureEcheanceFirst->setMontant($montant - $montantData);
                    $this->persist($factureEcheanceFirst);
                } elseif($montantData > $montant) {
                    $error = true;
                }
            }
        } elseif($reste > 0) {
            if($reste < $montantData) {
                $error = true;
            } 
        }

        $date = new \DateTime();
        $newFactureEcheance->setDateCreation($date);
        $newFactureEcheance->setFacture($facture);
        $newFactureEcheance->setStatus('encours');
        $newFactureEcheance->setMontant($montantData);
        $newFactureEcheance->setDelaiPaiement($delaiPaiement);
        $newFactureEcheance->setDateEcheance($datePaiement);
        $this->persist($newFactureEcheance);
        if($error) {
            //pas de flush
        }else {
            $this->update();
        }
        return [$newFactureEcheance, $error];
    }

    public function edit($factureEcheance = null)
    {
        $this->persist($factureEcheance);

        $facture = $factureEcheance->getFacture();
        $factureEcheances = $facture->getFactureEcheances();
        $montantHt = 0;

        foreach($factureEcheances as $facEcheance)
        {
            $montantHt += $facEcheance->getMontant();
        }
        $error = false;
        if($montantHt > $facture->getSolde())
        {
            $error = true;
        } else {
            $this->update();
        }

        return [$factureEcheance, $error];
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

    public function getLastValideFactureEcheance($facture = null)
    {
        return $this->entityManager->getRepository(FactureEcheance::class)->getLastValideFactureEcheance($facture);
    }

    public function remove($factureEcheance)
    {
        $this->entityManager->remove($factureEcheance);

        $this->update();
    }
}