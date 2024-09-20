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
use App\Entity\DatePeremptionProduct;
use App\Service\AuthorizationManager;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Exception\PropertyVideException;
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

        foreach ($products as $key => $product) { 
            // Gestion stock
            $produitCategorie = $product->getProduitCategorie();
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $stockEnKg = $stockRestant * $volumeGros; 
            $qtt = $product->getQtt(); 
            $stockEnKgReste = $stockEnKg - $qtt; 

            // Calcul du montant
            $factureDetail = new FactureDetail();
            $prix = 0;
            $prixVenteGros = null;
            $prixVenteDetail = null;
            $uniteVenteDetail = null;
            $uniteVenteGros = null;

            if ($product->getTypeVente() == "gros") {
                $montantHt  = $montantHt + (($qtt)  * $product->getPrixVenteGros());
                $prix = $product->getPrixVenteGros();
                $uniteVenteGros = $product->getUniteVenteGros();
                $prixVenteGros = $prix; 
                $stockRestant = $stockRestant - $qtt;

            } else {
                $montantHt  = $montantHt + ($qtt * $product->getPrixVenteDetail());
                $prix = $product->getPrixVenteDetail();
                $uniteVenteDetail = $product->getUniteVenteDetail();
                $prixVenteDetail = $prix;
                $stockRestant = $stockEnKgReste / $volumeGros; 
            }

            // Gérer le produit catégorie
            if($affaire->getPaiement() == "paye") {
                if($product->getTypeVente() == "detail") {
                    $qtt = $qtt / $volumeGros;
                }
                $sumQtt += $qtt;
            }

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
                $qttRestantEnKg = $qttRestant * $volumeGros; 

                if ($product->getTypeVente() == "detail") {
                    $qtt = $qtt / $volumeGros;
                }

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
                    $tabDatePeremption[] = $datePeremptionProduct;

                    //$tabQttRetenue[] = $qtt;
                    //$tabDatePeremption[] = $stk->getDatePeremption();
                    $qtt = 0;
                } else {
                    //$tabQttRetenue[] = $qttRestant;
                    //$tabDatePeremption[] = $stk->getDatePeremption();

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
                    $tabDatePeremption[] = $datePeremptionProduct;

                    $qtt -= $qttRestant;
                    $stk->setQttRestant(0);
                    $this->persist($stk);
                }

                $tabQttRestant[] = $stk->getQttRestant();
                $tabIdStock[] = $stk->getId();
                $tabQtt[] = $qtt;
            }
        }

        //dd(count($tabDatePeremption), $tabDatePeremption);

        $qttReserver = $produitCategorie->getQttReserver();

        $qttReserverString = (string)$qttReserver;

        if (strpos($qttReserverString, '.') !== false) {
            // Compter le nombre de caractères après la virgule
            $countAfterDecimal = strlen(substr($qttReserverString, strpos($qttReserverString, '.') + 1));
            $sumQtt = number_format($sumQtt, $countAfterDecimal, '.', '');
        } 

        if($qttReserver != null) {
            $produitCategorie->setQttReserver($qttReserver - $sumQtt);
        } else {
            $produitCategorie->setQttReserver($qttReserver);
        }

        //var_dump($qttReserver, $sumQtt);
        //dd($qttReserver, $sumQtt, $produitCategorie->getQttReserver());
        $this->entityManager->persist($produitCategorie);
        //dd($tabQttRestant);

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
   
    /*public function annuler($affaire = null, $folder = null)
    {
        $factures = $this->findByAffaire($affaire);
        $facture = $factures[0];
        $date = new \DateTime();
        
        $facture->setEtat('annule');
        $facture->setValid(true);
        $facture->setStatut('annule');
        $products = $affaire->getProducts();
        $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
        $tabQttRestant = [];
        $produitCategorie = null;

        foreach ($products as $key => $product) { 
            $produitCategorie = $product->getProduitCategorie();
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $qtt = $product->getQtt();
            
            if($product->getTypeVente() == "detail") {
                $qtt = $qtt / $volumeGros;
            }
            
            $stockRestant += $qtt;
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
            
            $datePeremptionProduct = $product->getDatePeremption();
            
            if($datePeremptionProduct) {
                $datePeremptionProduct = $datePeremptionProduct->format('d-m-Y');
            }
            
            $stocks = $this->entityManager->getRepository(Stock::class)->findByProduitCategorieAnulle($produitCategorie);
            $remainingQtt = $qtt; 
            $firstStockProcessed = false;

            foreach ($stocks as $keyS => $stock) {
                $datePeremptionStock = $stock->getDatePeremption();
                
                if($datePeremptionStock) {
                    $datePeremptionStock = $datePeremptionStock->getDate()->format('d-m-Y');
                }

                $qttStock = $stock->getQtt(); 
                $qttRestant = $stock->getQttRestant(); 

                if ($datePeremptionProduct && $datePeremptionStock && !$firstStockProcessed && $datePeremptionProduct === $datePeremptionStock) {
                    if ($remainingQtt + $qttRestant > $qttStock) {
                        $qttRestant = $qttStock;
                        $remainingQtt -= ($qttStock - $qttRestant);
                    } else {
                        $qttRestant += $remainingQtt;
                        $remainingQtt = 0;
                    }
                    $stock->setQttRestant($qttRestant);
                    $this->entityManager->persist($stock);
                    $firstStockProcessed = true;
                } elseif ($remainingQtt > 0) {
                    $qttRestant += $remainingQtt; 
                    if ($qttRestant > $qttStock) {
                        $remainingQtt -= ($qttStock - $qttRestant);
                        $qttRestant = $qttStock;
                    } else {
                        $remainingQtt = 0;
                    }
                    $stock->setQttRestant($qttRestant);
                    $this->entityManager->persist($stock);
                }

                $tabQttRestant[] = $stock->getQttRestant();
            }

            if ($remainingQtt > 0) {
                // Traitez ici la quantité restante non allouée
            }
        }

        //dd($tabQttRestant);


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
    }*/

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
        $tabQttRestant = [];
        $produitCategorie = null;

        foreach ($products as $key => $product) { 
            $produitCategorie = $product->getProduitCategorie();
            $stockRestant = $produitCategorie->getStockRestant();
            $volumeGros = $produitCategorie->getVolumeGros();
            $qtt = $product->getQtt();
            
            if($product->getTypeVente() == "detail") {
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
        }
        //dd($produitCategorie->getStockRestant());

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
}