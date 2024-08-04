<?php
namespace App\Service;

use App\Entity\User;
use Twig\Environment;
use App\Entity\Compte;
use App\Entity\Affaire;
use App\Entity\Facture;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Entity\ReglementFacture;
use App\Service\TCPDFService;
use Doctrine\ORM\EntityManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
use App\Repository\ReglementFactureRepository;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
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

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager, 
        ApplicationManager  $applicationManager, 
        TCPDFService $tcpdf, 
        Environment $twig,
        LoggerInterface $affaireLogger, 
        Security $security,
        ReglementFactureRepository  $reglementFactureRepository
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
    }

    public function add($affaire = null, $folder = null)
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
            'Produit' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);

        return $pdf;
    }

    public function annuler($affaire = null, $folder = null)
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
            'Produit' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);
        return $pdf;
    }
    public function update()
    {
        $this->entityManager->flush();
    }

    public function searchFactureRawSql($genre, $nom, $dateDu, $dateAu, $etat, $start, $limit, $order, $isCount, $search, $statutPaiement, $datePaieDu, $datePaieAu)
    {
        return $this->entityManager->getRepository(Facture::class)->searchFactureRawSql($genre, $nom, $dateDu,$dateAu, $etat, $limit, $start, $order, $isCount, $search, $statutPaiement, $datePaieDu, $datePaieAu);
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
}