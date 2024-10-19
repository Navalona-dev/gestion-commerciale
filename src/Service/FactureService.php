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
use App\Entity\Product;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Service\TCPDFService;
use App\Entity\DatePeremption;
use Doctrine\ORM\EntityManager;
use App\Entity\ProduitCategorie;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\ReglementFacture;
use App\Entity\DatePeremptionProduct;
use App\Repository\FactureRepository;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
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
    private $applicationRepo;
    private $factureRepository;

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
        LogService $logService,
        ApplicationRepository $applicationRepo,
        FactureRepository $factureRepository
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
        $this->applicationRepo = $applicationRepo;
        $this->factureRepository = $factureRepository;
    }

    public function add($affaire = null, $folder = null, $request = null, $applicationRevendeur = null)
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
        $facture->setNumero($numeroFacture);    
        $facture->setApplication($this->application);

        $facture->setEtat('regle');
        $facture->setValid(true);
        $facture->setStatut('regle');
        $facture->setDateCreation($date);
        $facture->setDate($date);
        $facture->setType("Facture");

        $affaire->setPaiement('paye');
        $affaire->setDatePaiement($date);
        $affaire->setDevisEvol('gagne');
        $affaire->setDateFacture($date);
        $affaire->setStatut("commande");
        $this->persist($affaire);

        $products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
        $montantHt = 0;
        $tabQttRestant = [];
        $produitCategorie = null;
        $tabIdStock = [];
        $sumQtt = 0;
        $stocks = null;
        $tabQttRetenue = [];
        $tabDatePeremption = [];
        $tabQtt = [];
        $tabQttReserver = [];
        $tabQttRestantProduitCategorie = [];
        $tabProduitCategorie = [];
        $quantitesParCategorie = [];
        $datePeremptionProductArray = [];

        foreach ($products as $key => $product) { 
            // Gestion stock
            $produitCategorie = $product->getProduitCategorie();
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $qtt = $product->getQtt(); 

            // Calcul du montant
            $factureDetail = new FactureDetail();
            $prix = 0;
            $prixVenteGros = null;
            $prixVenteDetail = null;
            $uniteVenteDetail = null;
            $uniteVenteGros = null;

            if ($product->getTypeVente() == "gros") {
                $montantHt  = ($montantHt + (($qtt)  * $product->getPrixVenteGros())) - $product->getRemise();
                $prix = $product->getPrixVenteGros();
                $uniteVenteGros = $product->getUniteVenteGros();
                $prixVenteGros = $prix; 

            } elseif($product->getTypeVente() == "detail") {
                $montantHt  = ($montantHt + ($qtt * $product->getPrixVenteDetail())) - $product->getRemise();
                $prix = $product->getPrixVenteDetail();
                $uniteVenteDetail = $product->getUniteVenteDetail();
                $prixVenteDetail = $prix;
                if($volumeGros > 0) {
                    $qtt = $qtt / $volumeGros; 
                }
            }

            $stockRestant = $stockRestant - $qtt;

            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);

            $factureDetail->setFacture($facture)
                          ->setReference($product->getReference())
                          ->setDetail($product->getProduitCategorie()->getNom())
                          ->setQtt($qtt)
                          ->setProduct($product)
                          ->setPrixUnitaire($prix)
                          ->setPrixTotal($montantHt)
                          ->setDescription($product->getDescription())
                          ->setUniteVenteDetail($uniteVenteDetail)
                          ->setUniteVenteGros($uniteVenteGros)
                          ->setPrixVenteDetail($prixVenteDetail)
                          ->setPrixVenteGros($prixVenteGros);
            $facture->addFactureDetail($factureDetail);
            $this->entityManager->persist($factureDetail);

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

            // Gestion des stocks par date de péremption
            $stocks = $this->entityManager->getRepository(Stock::class)->findByProductCategoryDatePeremption($produitCategorie);
            foreach ($stocks as $keyS => $stk) {
                $qttRestant = $stk->getQttRestant();

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

                $datePeremptionProductArray[] = $datePeremptionProduct;

                $tabQttRestant[] = $stk->getQttRestant();
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

            $tabQtt[] = $qttProduct; 
            $tabQttReserver[] = $produitCategorie->getQttReserver();
            $tabQttRestantProduitCategorie[] = number_format($produitCategorie->getStockRestant(),2,'.','');
           
             // Ajouter la quantité à la catégorie
            $produitCategorieId = $produitCategorie->getId();
            if (!isset($quantitesParCategorie[$produitCategorieId])) {
                $quantitesParCategorie[$produitCategorieId] = 0; // Initialiser si pas encore présent
            }
            $quantitesParCategorie[$produitCategorieId] += $qttProduct; // Additionner la quantité


            // Ajouter seulement si la produitCategorie n'est pas déjà dans le tableau
            $produitCategorieId = $produitCategorie->getId();
            if (!isset($tabProduitCategorie[$produitCategorieId])) {
                $tabProduitCategorie[$produitCategorieId] = $produitCategorie;
            }

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
            $data["stockRestant"] = $stockRestantFinal;
            $data["qtt"] = $qttReserverCommanderFinal;
            $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
            $data["typeSource"] = "Point de vente";
            $data["typeDestination"] = "Client";
            $data["commande"] = $affaire->getNom();
            $data["commandeId"] = $affaire->getId().'-paye';
            $data["sourceId"] =  $this->application->getId();
            $data["destinationId"] = $affaire->getCompte()->getId();
            $this->logService->addLog($request, "commande", $this->application->getId(), $produitCategorie->getReference(), $data);

        }

        //dd($produitCategorie->getQttReserverGros(), $produitCategorie->getQttReserverDetail(), $qttReserverCommanderFinal, $stockRestant, $stockRestantFinal);

        //dd($datePeremptionProductArray);
        //créer un nouveau produit dans l'application choisi lors de commande
        $newTabProduitCategorie = [];
        $newTabStock = [];
        //dd($applicationRevendeur);
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
                    $newProduitCategorie = $existingProduitCategorie;
                    $oldQttProduitCategorie = $existingProduitCategorie->getQtt();
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
        //dd(count($newTabStock));

        //dd($tabQtt, $tabQttReserver, $tabQttRestant, $tabQttRestantProduitCategorie);

        $facture->setFile($filename);
        $facture->setSolde($montantHt);
        $facture->setPrixHt($montantHt);    
        $facture->setReglement($montantHt);
        
        $this->persist($facture);

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

        // Load HTML content
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        $data['factureEcheances'] = null;
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
        $this->logger->info('Commande payée', [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getUserIdentifier() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);

        return [$pdfContent, $facture]; // Retourner le contenu PDF et l'objet facture
    }
   
    public function annuler($affaire = null, $folder = null, $request = null)
    {
        $factures = $this->findByAffaire($affaire);
        $facture = $factures[0];
        $date = new \DateTime();
        
        $facture->setEtat('annule');
        $facture->setValid(true);
        $facture->setStatut('annule');

        $factureEcheances = $facture->getFactureEcheances();

        if(count($factureEcheances) > 0) {
            foreach($factureEcheances as $factureEcheance) {
                $fileEcheance = $factureEcheance->getFile();
    
                $facEcheance = $this->factureRepository->findOneBy(['file' => $fileEcheance]);
                if($facEcheance){
                    $facEcheance->setStatut('annule');
                    $facEcheance->setEtat('annule');
                    $this->entityManager->persist($facEcheance);
                }
            }
        }

        if($affaire->isDepot() == true) {
            foreach($factures as $factureDepot){
                $factureDepot->setEtat('annule');
                $factureDepot->setValid(true);
                $factureDepot->setStatut('annule');
                $this->entityManager->persist($factureDepot);
            }
        }

        $products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
        $tabQttRestant = [];
        $produitCategorie = null;
        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();
        foreach ($products as $key => $product) { 
            $produitCategorie = $product->getProduitCategorie();
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $qtt = $product->getQtt();
            
            if($product->getTypeVente() == "detail" && $volumeGros > 0) {
                $qtt = $qtt / $volumeGros;
            }
            
            $stockRestant += $qtt;
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
            
            $dateperemptionProducts = $product->getDatePeremptionProducts();
            foreach($dateperemptionProducts as $datePeremptionProduct) {
                $qttRetenue = $datePeremptionProduct->getQttRetenue();
                $stock = $datePeremptionProduct->getStock();
                $qttRestantStock = $stock->getQttRestant();
                $newQttRestant = $qttRestantStock + $qttRetenue;
                $stock->setQttRestant($newQttRestant);
                $this->entityManager->persist($stock);
                $this->entityManager->remove($datePeremptionProduct);

            }
            
            $qttReserverCommander = $produitCategorie->getQttReserverCommander();
            if (null != $qttReserverCommander && $qttReserverCommander > 0) {
                $produitCategorie->setQttReserverCommander($qttReserverCommander - $qtt);
            }
            $this->persist($produitCategorie);

            //gerer le qtt reserver commander par sac et unité
            //$qttReserverCommander = $produitCategorie->getQttReserverCommander();
            $qttReserverCommander = $qtt;
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
             $data["dateReception"] = (new \DateTime())->format("d-m-Y h:i:s");
             $data["dateTransfert"] = null;
             $data["dateSortie"] = null;
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
             $data["commandeId"] = $affaire->getId().'-annule';
             $data["sourceId"] =  $this->application->getId();
             $data["destinationId"] = $affaire->getCompte()->getId();
             $this->logService->addLog($request, "commande", $this->application->getId(), $produitCategorie->getReference(), $data);
 

        }
        //dd($produitCategorie->getStockRestant(), $qttReserverCommanderFinal, $stockRestantFinal);
        
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
    
        // Définir le contenu du PDF
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        $data['factureEcheances'] = null;
        $data['application'] = $this->application;
        $data['user'] = $user;
        
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

    public function addDepot($affaire = null, $folder = null, $request = null)
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
        $facture->setNumero($numeroFacture);    
        $facture->setApplication($this->application);

        $facture->setEtat('encours');
        $facture->setValid(true);
        $facture->setStatut('encours');
        $facture->setDateCreation($date);
        $facture->setDate($date);
        $facture->setType("Facture");

        $affaire->setPaiement('endepot');
        $affaire->setDatePaiement($date);
        $affaire->setDevisEvol('encours');
        $affaire->setDateFacture($date);
        $affaire->setStatut("commande");
        $this->persist($affaire);

        $products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
        $montantHt = 0;
        $tabQttRestant = [];
        $produitCategorie = null;
        $tabIdStock = [];
        $sumQtt = 0;
        $stocks = null;
        $tabQttRetenue = [];
        $tabDatePeremption = [];
        $tabQtt = [];
        $tabQttReserver = [];
        $tabQttRestantProduitCategorie = [];
        $tabProduitCategorie = [];
        $quantitesParCategorie = [];
        $datePeremptionProductArray = [];

        foreach ($products as $key => $product) { 
            // Gestion stock
            $produitCategorie = $product->getProduitCategorie();
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $qtt = $product->getQtt(); 

            // Calcul du montant
            $factureDetail = new FactureDetail();
            $prix = 0;
            $prixVenteGros = null;
            $prixVenteDetail = null;
            $uniteVenteDetail = null;
            $uniteVenteGros = null;

            if ($product->getTypeVente() == "gros") {
                $montantHt  = ($montantHt + (($qtt)  * $product->getPrixVenteGros())) - $product->getRemise();
                $prix = $product->getPrixVenteGros();
                $uniteVenteGros = $product->getUniteVenteGros();
                $prixVenteGros = $prix; 

            } elseif($product->getTypeVente() == "detail") {
                $montantHt  = ($montantHt + ($qtt * $product->getPrixVenteDetail())) - $product->getRemise();
                $prix = $product->getPrixVenteDetail();
                $uniteVenteDetail = $product->getUniteVenteDetail();
                $prixVenteDetail = $prix;
                if($volumeGros > 0) {
                    $qtt = $qtt / $volumeGros; 
                }
            }

            $stockRestant = $stockRestant - $qtt;

            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);

            $factureDetail->setFacture($facture)
                          ->setReference($product->getReference())
                          ->setDetail($product->getProduitCategorie()->getNom())
                          ->setQtt($qtt)
                          ->setProduct($product)
                          ->setPrixUnitaire($prix)
                          ->setPrixTotal($montantHt)
                          ->setDescription($product->getDescription())
                          ->setUniteVenteDetail($uniteVenteDetail)
                          ->setUniteVenteGros($uniteVenteGros)
                          ->setPrixVenteDetail($prixVenteDetail)
                          ->setPrixVenteGros($prixVenteGros);
            $facture->addFactureDetail($factureDetail);
            $this->entityManager->persist($factureDetail);

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

            // Gestion des stocks par date de péremption
            $stocks = $this->entityManager->getRepository(Stock::class)->findByProductCategoryDatePeremption($produitCategorie);
            foreach ($stocks as $keyS => $stk) {
                $qttRestant = $stk->getQttRestant();

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

                $datePeremptionProductArray[] = $datePeremptionProduct;

                $tabQttRestant[] = $stk->getQttRestant();
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

            $tabQtt[] = $qttProduct; 
            $tabQttReserver[] = $produitCategorie->getQttReserver();
            $tabQttRestantProduitCategorie[] = number_format($produitCategorie->getStockRestant(),2,'.','');
           
             // Ajouter la quantité à la catégorie
            $produitCategorieId = $produitCategorie->getId();
            if (!isset($quantitesParCategorie[$produitCategorieId])) {
                $quantitesParCategorie[$produitCategorieId] = 0; // Initialiser si pas encore présent
            }
            $quantitesParCategorie[$produitCategorieId] += $qttProduct; // Additionner la quantité


            // Ajouter seulement si la produitCategorie n'est pas déjà dans le tableau
            $produitCategorieId = $produitCategorie->getId();
            if (!isset($tabProduitCategorie[$produitCategorieId])) {
                $tabProduitCategorie[$produitCategorieId] = $produitCategorie;
            }

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
            $data["stockRestant"] = $stockRestantFinal;
            $data["qtt"] = $qttReserverCommanderFinal;
            $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
            $data["typeSource"] = "Point de vente";
            $data["typeDestination"] = "Client";
            $data["commande"] = $affaire->getNom();
            $data["commandeId"] = $affaire->getId().'-paye';
            $data["sourceId"] =  $this->application->getId();
            $data["destinationId"] = $affaire->getCompte()->getId();
            $this->logService->addLog($request, "commande", $this->application->getId(), $produitCategorie->getReference(), $data);

        }

        $facture->setFile($filename);
        $facture->setSolde($montantHt);
        $facture->setPrixHt($montantHt);    
        //$facture->setReglement($montantHt);
        
        $this->persist($facture);

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

        // Load HTML content
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        $data['factureEcheances'] = null;
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
        $this->logger->info('Commande payée', [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getUserIdentifier() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);

        return [$pdfContent, $facture]; // Retourner le contenu PDF et l'objet facture
    }

    public function validDepot($affaire = null, $folder = null, $request = null, $product = null, $qttVendu = null)
    {   
        $factures = $affaire->getFactures();
        $factureParent = $factures[0];
        $factureParentNumero = $factureParent->getNumero();

        $facture = Facture::newFacture($affaire);
        $date = new \DateTime();

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();
        
        // Initialiser le numéro du dépôt au plus grand nombre existant
        $suffixeMax = 0;

        foreach ($factures as $existingFacture) {
            // Si la facture a déjà un depotNumero (vérifiez si ce champ existe et est utilisé pour les dépôts)
            if ($existingFacture->getDepotNumero() !== null) {
                $depotNumero = $existingFacture->getDepotNumero();
                if ($depotNumero > $suffixeMax) {
                    $suffixeMax = $depotNumero; // Mettre à jour le plus grand numéro trouvé
                }
            }
        }

        // Incrémenter le suffixe pour le nouveau dépôt
        $nextDepotNumero = $suffixeMax + 1;

        $facture->setNumero($factureParentNumero);
        $facture->setDepotNumero($nextDepotNumero);
        $facture->setApplication($this->application);

        $facture->setEtat('regle');
        $facture->setValid(true);
        $facture->setStatut('regle');
        $facture->setDateCreation($date);
        $facture->setDate($date);
        $facture->setType("Facture");
        $facture->setDepot(true);

        $products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
       
        $prix = 0;

        //gerer le produit 
        if($product->getTypeVente() == "detail") {
            $prix = $product->getProduitCategorie()->getPrixVenteDetail();
        } elseif($product->getTypeVente() == "gros") {
            $prix = $product->getProduitCategorie()->getPrixVenteGros();
        }

        $montantHt = $product->getQtt() * $prix;
        $sumQtt = 0;
        $sumQttVendu = 0;

        if($product->getQttVendu() != null) {
            $product->setQttVendu($qttVendu + $product->getQttVendu());
        } else {
            $product->setQttVendu($qttVendu);
        }

        $qttCommander = $product->getQtt();
        $product->setQttRestant($qttCommander - $product->getQttVendu());
        $product->setDejaPaye($product->getQttVendu() * $prix);
        $product->setRestePayer($montantHt - $product->getDejaPaye());

        $this->entityManager->persist($product);

        foreach($products as $produit) {
            $qtt = $produit->getQtt();
            $sumQtt += $qtt;
            $qttVendus = $produit->getQttVendu();
            $sumQttVendu += $qttVendus;
        }

        if($sumQtt == $sumQttVendu) {
            $affaire->setPaiement('paye');
            $affaire->setDevisEvol('gagne');
            $factureParent->setStatut('termine');
            $factureParent->setEtat('termine');
            $factureParent->setDateReglement(new \DateTime());
        }else {
            $affaire->setPaiement('endepot');
            $affaire->setDevisEvol('encours');
        }

        if($factureParent->getReglement() != null) {
            $factureParent->setReglement($factureParent->getReglement() + ($qttVendu * $prix));
        } else {
            $factureParent->setReglement($qttVendu * $prix);
        }

        $this->entityManager->persist($factureParent);

        $affaire->setDatePaiement($date);
        $affaire->setDateFacture($date);
        $affaire->setStatut("commande");
        $this->persist($affaire);

        $montantFinal = $qttVendu * $prix;

        $facture->setFile($filename);
        $facture->setSolde($montantFinal);
        $facture->setPrixHt($montantFinal);    
        $facture->setReglement($montantFinal);    
        
        $this->persist($facture);
        //dd($facture->getSolde());

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

        // Load HTML content
        $data = [];
        $data['produits'] = $products;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        $data['factureEcheances'] = null;
        $data['application'] = $this->application;
        $data['user'] = $user;
        $data['produit'] = $product;
        
        $html = $this->twig->render('admin/facture/factureDepotPdf.html.twig', $data);

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

    public function validDepotMultiple($affaire = null, $folder = null, $request = null, $qttVendusSelection = null, $productsSelectionner = null)
    {   //dd($qttVendusSelection);
        $factures = $affaire->getFactures();
        $factureParent = $factures[0];
        $factureParentNumero = $factureParent->getNumero();

        $facture = Facture::newFacture($affaire);
        $date = new \DateTime();

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();
        
        // Initialiser le numéro du dépôt au plus grand nombre existant
        $suffixeMax = 0;

        foreach ($factures as $existingFacture) {
            // Si la facture a déjà un depotNumero (vérifiez si ce champ existe et est utilisé pour les dépôts)
            if ($existingFacture->getDepotNumero() !== null) {
                $depotNumero = $existingFacture->getDepotNumero();
                if ($depotNumero > $suffixeMax) {
                    $suffixeMax = $depotNumero; // Mettre à jour le plus grand numéro trouvé
                }
            }
        }

        // Incrémenter le suffixe pour le nouveau dépôt
        $nextDepotNumero = $suffixeMax + 1;

        $facture->setNumero($factureParentNumero);
        $facture->setDepotNumero($nextDepotNumero);
        $facture->setApplication($this->application);

        $facture->setEtat('regle');
        $facture->setValid(true);
        $facture->setStatut('regle');
        $facture->setDateCreation($date);
        $facture->setDate($date);
        $facture->setType("Facture");
        $facture->setDepot(true);

        //$products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
       
        $prix = 0;
        $sumQtt = 0;
        $sumQttVendu = 0;
        $qttVendu = 0;
        $montantHtTotal = 0;

        /*$tabQttVendu = [];
        $tabQttRestant = [];
        $tabQtt = [];
        $tabRestePayer = [];
        $tabDejaPaye = [];
        $tabPaieNOw = [];
        $tabMontantHt = [];*/


        foreach($productsSelectionner as $key => $product) {
            //dd($qttVendusSelection[$key]);
            $qttVendu = $qttVendusSelection[$key];
            
            if($product->getTypeVente() == "detail") {
                $prix = $product->getProduitCategorie()->getPrixVenteDetail();
            } elseif($product->getTypeVente() == "gros") {
                $prix = $product->getProduitCategorie()->getPrixVenteGros();
            }

            $qtt = $product->getQtt();
            $sumQtt += $qtt;
            //$qttVendus = $product->getQttVendu();
            $sumQttVendu += $qttVendu;

            if($product->getQttVendu() != null) {
                $product->setQttVendu($qttVendu + $product->getQttVendu());
            } else {
                $product->setQttVendu($qttVendu);
            }

            $montantHt = $product->getQtt() * $prix;

            $qttCommander = $product->getQtt();
            $product->setQttRestant($qttCommander - $product->getQttVendu());
            $product->setDejaPaye($product->getQttVendu() * $prix);
            $product->setRestePayer($montantHt - $product->getDejaPaye());

            $this->entityManager->persist($product);

            if($factureParent->getReglement() != null) {
                $factureParent->setReglement($factureParent->getReglement() + ($qttVendu * $prix));
            } else {
                $factureParent->setReglement($qttVendu * $prix);
            }
    
            $this->entityManager->persist($factureParent);

            /*if($product->getQtt() == $qttVendu) {
                $montantHtTotal += $montantHt;
            } else {
                $montantHtTotal += ($montantHt - ($qttVendu * $prix));
            }*/

            $montantHtTotal += $qttVendu * $prix;


            /*$tabQttVendu[] = $product->getQttVendu();
            $tabQttRestant[] = $product->getQttRestant();
            $tabQtt[] = $product->getQtt();
            $tabRestePayer[] = $product->getRestePayer();
            $tabDejaPaye[] = $product->getDejaPaye();
            $tabPaieNOw[] = $product->getQttVendu() * $prix;
            $tabMontantHt[] = $product->getQtt() * $prix;*/
        }

        //dd($tabMontantHt, $tabQtt, $tabQttVendu, $tabQttRestant, $tabDejaPaye, $tabRestePayer, $tabPaieNOw);

        if($sumQtt == $sumQttVendu) {
            $affaire->setPaiement('paye');
            $affaire->setDevisEvol('gagne');
            $factureParent->setStatut('termine');
            $factureParent->setEtat('termine');
            $factureParent->setDateReglement(new \DateTime());
        }else {
            $affaire->setPaiement('endepot');
            $affaire->setDevisEvol('encours');
        }

        
        $affaire->setDatePaiement($date);
        $affaire->setDateFacture($date);
        $affaire->setStatut("commande");
        $this->persist($affaire);

        $facture->setFile($filename);
        $facture->setSolde($montantHtTotal);
        $facture->setPrixHt($montantHtTotal);    
        $facture->setReglement($montantHtTotal);    
        
        $this->persist($facture);
        //dd($facture->getSolde());

        //dd($facture->getSolde(), $facture->getPrixHt(), $facture->getReglement());

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

        // Load HTML content
        $data = [];
        $data['produits'] = $productsSelectionner;
        $data['facture'] = $facture;
        $data['compte'] = $facture->getCompte();
        $data['factureEcheances'] = null;
        $data['application'] = $this->application;
        $data['user'] = $user;
        
        $html = $this->twig->render('admin/facture/factureDepotMultiplePdf.html.twig', $data);

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
}