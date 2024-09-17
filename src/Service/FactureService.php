<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Stock;
use Twig\Environment;
use App\Entity\Compte;
use App\Entity\Affaire;
use App\Entity\Facture;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Service\TCPDFService;
use Doctrine\ORM\EntityManager;
use App\Entity\ReglementFacture;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
use Symfony\Component\Security\Core\Security;
use App\Repository\ReglementFactureRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FactureService
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

    /*public function add($affaire = null, $folder = null)
    {
        $facture = Facture::newFacture($affaire);
        $pdf = $this->tcpdf;
        $date = new \DateTime();
        
        $numeroFacture = 1;
        $tabNumeroFacture = $this->getLastValideFacture();
        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }
        $facture->setNumero($numeroFacture);    
        $facture->setApplication($this->application);
       
        $facture->setEtat('regle');
        $facture->setValid(true);
        $facture->setStatut('regle');
        $facture->setDateCreation($date);
        $facture->setDate($date);
        $facture->setType("Facture");
        $products = $affaire->getProducts();
        $filename = "Facture(FA-" . $facture->getNumero() . ").pdf";
        $montantHt = 0;

        // Sortie du PDF sous forme de réponse HTTP
        
        foreach ($products as $key => $product) { 
            $factureDetail = new FactureDetail();
            $prix = 0;
            $prixVenteGros = null;
            $prixVenteDetail = null;
            $uniteVenteDetail = null;
            $uniteVenteGros = null;

            // Gestion stock
            $produitCategorie = $product->getProduitCategorie();
            $stock = $produitCategorie->getStockRestant();
            
            $qtt = $product->getQtt();
            $stock = $stock - $qtt;
            $produitCategorie->setStockRestant($stock);
          
            $this->persist($produitCategorie);

            //gestion de notification
            $stockMin = $produitCategorie->getStockMin();
            $stockRestant = $produitCategorie->getStockRestant();

            if($stockRestant <= $stockMin) {
                $notification = new Notification();
                $message = 'Le stock du produit ' . '<strong>' . $produitCategorie->getNom() . '</strong>' . ' est presque épuisé, vueillez ajouter un ou plusieurs!!';
                $notification->setMessage($message)
                             ->setDateCreation(new \DateTime())
                             ->setApplication($this->application)
                             ->setProduitCategorie($produitCategorie)
                             ->setStockMin(true);
                $this->persist($notification);
            }

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
            //$factureDetail->setTva($tva);
            $factureDetail->setPrixUnitaire($prix);
            $factureDetail->setPrixTotal($montantHt);
           
            $factureDetail->setDescription($product->getDescription());
            //$factureDetail->setRemise($remise);
            $factureDetail->setUniteVenteDetail($uniteVenteDetail);
            $factureDetail->setUniteVenteGros($uniteVenteGros);
            $factureDetail->setPrixVenteDetail($prixVenteDetail);
            $factureDetail->setPrixVenteGros($prixVenteGros);


            $facture->addFactureDetail($factureDetail);

            $this->persist($factureDetail);
        }
        
        $facture->setFile($filename);
        $facture->setSolde($montantHt);
        $facture->setPrixHt($montantHt);	
        $facture->setReglement($montantHt);
        
        $reglementFacture = new ReglementFacture();
        $reglementFacture->setFacture($facture);
        $reglementFacture->setMontant($montantHt);
        $reglementFacture->setDateReglement($date);
        $reglementFacture->setNumero(1);
        $reglementFacture->setNumeroFacture($numeroFacture);
        $reglementFacture->setToutPaye(true);
        $facture->addReglementFacture($reglementFacture);
        $this->persist($reglementFacture);
        $this->persist($facture);

        $affaire->setPaiement('paye');
        $affaire->setDatePaiement($date);
        $affaire->setDevisEvol('gagne');
        $affaire->setDateFacture($date);
        $affaire->setStatut("commande");
        $this->persist($affaire);
        $this->update();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('CVB');
        $pdf->SetTitle('Facture');
        //$pdf->SetSubject('Hello, je teste seulement le PDF en utilisant ce bundle TCPDF');
        //$pdf->SetKeywords('PDF');

        // Ajouter une page
        $pdf->AddPage();

        // Définir le contenu du PDF
        
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        
        $fileName = $folder . $filename;

        $html = $this->twig->render('admin/facture/facturePdf.html.twig', $data);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($fileName, 'F');
        unset($facture);
        
        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer le log
        $this->logger->info('Commande payée', [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);

        return $pdf;
    }*/

    public function add($affaire = null, $folder = null, $request = null)
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

        $facture->setEtat('regle');
        $facture->setValid(true);
        $facture->setStatut('regle');
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
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $stockEnKg = $stockRestant * $volumeGros; 

            
            $qtt = $product->getQtt(); 
            $stockEnKgReste = $stockEnKg - $qtt; 
            $stockRestant = $stockEnKgReste / $volumeGros; 
            $produitCategorie->setStockRestant($stockRestant);
            
            $this->entityManager->persist($produitCategorie);
          
            $factureDetail = new FactureDetail();
            $prix = 0;
            $prixVenteGros = null;
            $prixVenteDetail = null;
            $uniteVenteDetail = null;
            $uniteVenteGros = null;

            if ($product->getTypeVente() == "gros") {
                $montantHt  = $montantHt + (($qtt / $product->getProduitCategorie()->getVolumeGros())  * $product->getPrixVenteGros());
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

            // Gestion de notification
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
        $facture->setReglement($montantHt);
        
        $this->persist($facture);
        $affaire->setPaiement('paye');
        $affaire->setDatePaiement($date);
        $affaire->setDevisEvol('gagne');
        $affaire->setDateFacture($date);
        $affaire->setStatut("commande");
        $this->persist($affaire);
        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer log
        $this->logger->info('Facture ajouté', [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);
        $this->update();
        
        // Initialize Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $pdf = new Dompdf($options);

        $factureEcheances = $facture->getFactureEcheances();
        $factureEcheanceFirst = null;
        if(count($factureEcheances) > 0) {
            $factureEcheanceFirst = $factureEcheances[0];
        }

        // Load HTML content
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        $data['factureEcheances'] = null;
        $data['factureEcheanceFirst'] = $factureEcheanceFirst;
        
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
        $this->logger->info('Commande payée', [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getUserIdentifier() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);

        return [$pdfContent, $facture]; // Retourner le contenu PDF et l'objet facture
    }
   
    public function annuler($affaire = null, $folder = null)
    {
        $factures = $this->findByAffaire($affaire);
        $facture = $factures[0];
        $date = new \DateTime();
        
        $facture->setEtat('annule');
        $facture->setValid(true);
        $facture->setStatut('annule');
        $products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
    
        foreach ($products as $key => $product) { 
            // Gestion du stock
            $produitCategorie = $product->getProduitCategorie();
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $stockEnKg = $volumeGros * $stockRestant; 

            $qtt = $product->getQtt(); 
            $stockEnKg += $qtt; 
            $stockRestant = $stockEnKg / $volumeGros;
            $produitCategorie->setStockRestant($stockRestant);
        
            $this->entityManager->persist($produitCategorie);
        
            $datePeremptionProduct = $product->getDatePeremption();
            if($datePeremptionProduct)
            {
                $datePeremptionProduct->format('d-m-Y');
            }
        
            $stocks = $this->entityManager->getRepository(Stock::class)->findByProduitCategorieDatePerremption($produitCategorie);
        
            // Trier les stocks par date de péremption (de la plus proche à la plus éloignée)
            usort($stocks, function($a, $b) {
                $dateA = $a->getDatePeremption()->getDate();
                $dateB = $b->getDatePeremption()->getDate();
                return $dateA <=> $dateB;
            });
        
            $remainingQtt = $product->getQtt(); 
        
            $firstStockProcessed = false;
        
            foreach ($stocks as $stock) {
                $datePeremptionStock = $stock->getDatePeremption();
                if($datePeremptionStock) {
                    $datePeremptionStock->getDate()->format('d-m-Y');
                }
                $qttStock = $stock->getQtt(); 
                $qttRestant = $stock->getQttRestant(); 
        
                if ($datePeremptionProduct && $datePeremptionStock && !$firstStockProcessed && $datePeremptionProduct == $datePeremptionStock) {
                    // Pour le premier stock qui correspond à la date de péremption
                    if ($remainingQtt + $qttRestant > $qttStock) {
                        $qttRestant = $qttStock; // Utiliser la quantité totale du stock
                        $remainingQtt -= $qttStock; // Réduire la quantité restante
                    } else {
                        $qttRestant += $remainingQtt; // Ajouter le reste de la quantité
                        $remainingQtt = 0; // Toute la quantité a été réduite
                    }
                    $stock->setQttRestant($qttRestant);
                    $this->entityManager->persist($stock);
        
                    $firstStockProcessed = true;
        
                    // Si toute la quantité est traitée, sortir de la boucle
                    if ($remainingQtt <= 0) {
                        break;
                    }
                } elseif ($remainingQtt > 0) {
                    // Pour les stocks suivants ou si la date de péremption ne correspond pas
                    $qttRestant += $remainingQtt; // Ajouter le reste de la quantité au stock suivant
        
                    if ($qttRestant > $qttStock) {
                        $qttRestant = $qttStock; // Ne pas dépasser la capacité du stock
                        $remainingQtt -= $qttStock; // Réduire la quantité restante
                    } else {
                        $remainingQtt = 0; // Toute la quantité a été réduite
                    }
                    $stock->setQttRestant($qttRestant);
                    $this->entityManager->persist($stock);
                } else {
                    $qttRestant += $remainingQtt; // Ajouter le reste de la quantité au stock suivant
        
                    if ($qttRestant > $qttStock) {
                        $qttRestant = $qttStock; // Ne pas dépasser la capacité du stock
                        $remainingQtt -= $qttStock; // Réduire la quantité restante
                    } else {
                        $remainingQtt = 0; // Toute la quantité a été réduite
                    }
                    $stock->setQttRestant($qttRestant);
                    $this->entityManager->persist($stock);
                }
            }
        
            // Si après la boucle, il reste encore des quantités à traiter,
            // il peut être nécessaire de gérer cela en fonction de vos besoins
            if ($remainingQtt > 0) {
                // Gérer les quantités restantes qui n'ont pas pu être stockées
                // Par exemple, enregistrer un message d'erreur ou ajuster les stocks supplémentaires
            }
        }

        $this->persist($facture);
        $affaire->setDateAnnule($date);
        $affaire->setDevisEvol('perdu');
        $affaire->setPaiement('annule');
        $this->persist($affaire);
        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer log
        $this->logger->info('Facture annulé', [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);
        $this->update();
        
        // Créer une instance de Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $pdf = new Dompdf($options);

        $factureEcheances = $facture->getFactureEcheances();
        $factureEcheanceFirst = $factureEcheances[0];
    
        // Définir le contenu du PDF
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        $data['factureEcheances'] = null;
        $data['factureEcheanceFirst'] = $factureEcheanceFirst;
        
        $html = $this->twig->render('admin/facture/facturePdf.html.twig', $data);
        
        // Charger le contenu HTML dans dompdf
        $pdf->loadHtml($html);
        
        // (Optionnel) Configurer la taille du papier et l'orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Rendre le PDF
        $pdf->render();
        
        // Obtenir le contenu PDF
        $output = $pdf->output();
        
        // Vous pouvez choisir de sauvegarder le fichier sur le serveur si nécessaire
        // file_put_contents($folder . $filename, $output);
        
        return [$output, $facture]; // Retourner le contenu PDF et l'objet facture
    }
    

    /*public function annuler($affaire = null, $folder = null)
    {
        $factures = $this->findByAffaire($affaire);
        $facture = $factures[0]; 
        $pdf = $this->tcpdf;
        $date = new \DateTime();
        
        
        $facture->setEtat('annule');
        $facture->setValid(true);
        $facture->setStatut('annule');
        $products = $affaire->getProducts();
        $filename = "Facture(FA-Annuler-" . $facture->getNumero() . ").pdf";
        $facture->setFile($filename);
        // Sortie du PDF sous forme de réponse HTTP
        foreach ($products as $key => $product) { 
            // Gestion stock
            $produitCategorie = $product->getProduitCategorie();
            $stock = $produitCategorie->getStockRestant();
            
            $qtt = $product->getQtt();
            $stock = $stock + $qtt;
            $produitCategorie->setStockRestant($stock);
          
            $this->persist($produitCategorie);
            
        }
        
       
        $this->persist($facture);
        $affaire->setDateAnnule($date);
        $affaire->setDevisEvol('perdu');
        $affaire->setPaiement('annule');
        $this->persist($affaire);
        $this->update();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('CVB');
        $pdf->SetTitle('Facture');
        //$pdf->SetSubject('Hello, je teste seulement le PDF en utilisant ce bundle TCPDF');
        //$pdf->SetKeywords('PDF');

        // Ajouter une page
        $pdf->AddPage();

        // Définir le contenu du PDF
        
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        
        $fileName = $folder . $filename;

        $html = $this->twig->render('admin/facture/facturePdf.html.twig', $data);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($fileName, 'F');
        unset($facture);

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer le log
        $this->logger->info('Commande annulée', [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);
        return $pdf;
    }*/

    public function update()
    {
        $this->entityManager->flush();
    }

    public function searchFactureRawSql($genre, $nom, $dateDu, $dateAu, $etat, $start, $limit, $order, $isCount, $search, $statutPaiement, $datePaieDu, $datePaieAu, $tabIdFactureFiltered)
    {
        return $this->entityManager->getRepository(Facture::class)->searchFactureRawSql($genre, $nom, $dateDu,$dateAu, $etat, $limit, $start, $order, $isCount, $search, $statutPaiement, $datePaieDu, $datePaieAu, $tabIdFactureFiltered);
    }

    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    public function remove($entity)
    {
        $this->entityManager->remove($entity);
    }

    public function findByAffaire($affaire)
    {
        return $this->entityManager->getRepository(Facture::class)->findBy(['affaire' => $affaire]);
    }

    public function find($id)
    {
        return $this->entityManager->getRepository(Facture::class)->find($id);
    }

    public function getAllAffaire($compte = null, $start = 1, $limit = 0, $statut = null)
    {
        return $this->entityManager->getRepository(Facture::class)->searchAffaire($compte, null,null, $limit, $start, $statut);
    }
    
    public function getNombreTotalCompte()
    {
        return $this->entityManager->getRepository(Facture::class)->countAll();
    }

    public function getLastValideFacture()
    {
        return $this->entityManager->getRepository(Facture::class)->getLastValideFacture();
    }

    public function getAllFactures()
    {
        $factures = $this->entityManager->getRepository(Facture::class)->getAllFactures();
        if (count($factures) > 0) {
            return $factures;
        }
        return false;
    }

    public function getAllFacturesByAffaire($affaireId = null)
    {
        $factures = $this->entityManager->getRepository(Facture::class)->getAllFacturesByAffaire($affaireId);
        if (count($factures) > 0) {
            return $factures;
        }
        return false;
    }

    public function delete($facture = null)
    {
        $factureDetails = $facture->getFactureDetails();
        foreach($factureDetails as $factureDetail) {
            $this->remove($factureDetail);
        }

        $factureEcheances = $facture->getFactureEcheances();
        foreach($factureEcheances as $factureEcheance) {
            $this->remove($factureEcheance);
        }

        $this->remove($facture);

        $this->update();
        
        return $facture;
        
    }
}