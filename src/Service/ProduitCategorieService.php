<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\Transfert;
use App\Entity\ProduitType;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use App\Entity\ProduitCategorie;
use App\Repository\CompteRepository;
use App\Service\AuthorizationManager;
use App\Repository\CategorieRepository;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitTypeRepository;
use App\Exception\ActionInvalideException;
use App\Repository\ProductImageRepository;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProduitCategorieService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $session;
    public  $isCurrentDossier = false;
    private $categorieRepo;
    private $typeRepo;
    private $imageRepo;
    private $compteRepo;
    private $logger;
    private $security;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        CategorieRepository $categorieRepo,
        ProduitTypeRepository $typeRepo,
        ProductImageRepository $imageRepo,
        CompteRepository $compteRepo,
        LoggerInterface $productLogger, 
        Security $security)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->categorieRepo = $categorieRepo;
        $this->typeRepo = $typeRepo;
        $this->imageRepo = $imageRepo;
        $this->compteRepo = $compteRepo;
        $this->logger = $productLogger;
        $this->security = $security;
    }

    public function add($instance)
    {
        $produitCategorie = ProduitCategorie::newProduitCategorie($instance);

        $date = new \DateTime();

        $produitCategorie->setApplication($instance->getApplication());
        $produitCategorie->setDateCreation($date);
        $produitCategorie->setDescription($instance->getDescription());
        $produitCategorie->setReference($instance->getReference());
        $produitCategorie->setPrixHt($instance->getPrixHt());
        $produitCategorie->setTva($instance->getTva());
        $produitCategorie->setQtt($instance->getQtt());
        $produitCategorie->setStockRestant($instance->getStockRestant());
        $produitCategorie->setStockMin(10);
        $produitCategorie->setStockMax(50);
        $produitCategorie->setUniteVenteGros($instance->getUniteVenteGros());
        $produitCategorie->setUniteVenteDetail($instance->getUniteVenteDetail());
        $produitCategorie->setPrixVenteGros($instance->getPrixVenteGros());
        $produitCategorie->setPrixVenteDetail($instance->getPrixVenteDetail());
        $produitCategorie->setPrixTTC($instance->getPrixTTC());
        $produitCategorie->setPrixAchat($instance->getPrixAchat());
        $produitCategorie->setCategorie($instance->getCategorie());

        foreach($produitCategorie->getProductImages() as $productImage) {
            $productImage->setProduitCategorie($produitCategorie);
            $productImage->setDateCreation($date);
            $this->entityManager->persist($productImage);
        }

        $stock = new Stock();

        if($produitCategorie->getQtt()) {
            $qtt = $produitCategorie->getQtt();
        } else {
            $qtt = 0;
        }

        $stock->setQtt($qtt);
        $stock->setProduitCategorie($produitCategorie);
        $stock->setDateCreation($date);

        $this->entityManager->persist($stock);

        $this->entityManager->persist($produitCategorie);

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer log
        $this->logger->info('Produit catégorie ajouté', [
            'Produit' => $produitCategorie->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $produitCategorie->getApplication()->getId()
        ]);

        $this->update();
        unset($instance);
        return $produitCategorie;
    }

    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($produitCategorie)
    {
        $stocks = $produitCategorie->getStocks();

        foreach($stocks as $stock) {
            $this->entityManager->remove($stock);
        }

        $images = $produitCategorie->getProductImages();

        foreach($images as $image) {
            $this->entityManager->remove($image);
        }

        $this->entityManager->remove($produitCategorie);
        
         // Obtenir l'utilisateur connecté
         $user = $this->security->getUser();

         // Créer log
         $this->logger->info('Produit catégorie supprimé', [
             'Produit' => $produitCategorie->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
             'ID Application' => $produitCategorie->getApplication()->getId()
         ]);

        $this->update();
    }

    public function getAllProduitCategories($affairesProduct = [])
    {
        $produitCategories = $this->entityManager->getRepository(ProduitCategorie::class)->getProduits($affairesProduct);
        if ($produitCategories != false  && count($produitCategories) > 0) {
            return $produitCategories;
        }
        return false;
    }

    public function getCategorieById($id)
    {
        $produitCategorie = $this->entityManager->getRepository(ProduitCategorie::class)->find($id);
        if ($produitCategorie) {
            return $produitCategorie;
        }
        return false;
    }

    public function getAllFournisseur()
    {
        $produitCategorie = $this->entityManager->getRepository(ProduitCategorie::class)->getAllFournisseur();
        if ($produitCategorie) {
            return $produitCategorie;
        }
        return false;
    }

    public function getFournisseurById($id)
    {
        $compte = $this->entityManager->getRepository(Compte::class)->find($id);
        if ($compte) {
            return $compte;
        }
        return false;
    }

    public function addTransfert($produitCategorie, $newApplication, $quantity)
    {
        $transfert = new Transfert();
        $transfert->setProduitCategorie($produitCategorie);
        $transfert->setOldApplication($produitCategorie->getApplication());
        $transfert->setApplication($newApplication);
        $transfert->setQuantity($quantity);
        $transfert->setDateCreation(new \DateTime());

        $this->entityManager->persist($transfert);
    }

    
    public function updateStockRestant($oldProduitCategorie, $quantity)
    {
        $oldStockRestant = $oldProduitCategorie->getStockRestant();
        $newStockRestant = $oldStockRestant - $quantity;
        
        if ($newStockRestant < 0) {
            $oldProduitCategorie->setStockRestant(0);
        } else {
            $oldProduitCategorie->setStockRestant($newStockRestant);
        }
        
        $this->entityManager->persist($oldProduitCategorie);

        
    }

    public function addNewProductForNewApplication($productReferenceExists, $oldProduitCategorie, $quantity, $application, $isChangePrice)
    {
        $newProduitCategorie = $productReferenceExists ? $productReferenceExists : new ProduitCategorie();
        $date = new \DateTime();
        $categorie = $oldProduitCategorie->getCategorie();
        $type = $oldProduitCategorie->getType();

        $existingCategorie = $this->categorieRepo->findOneBy(['nom' => $categorie->getNom(), 'application' => $application]);
        $existingType = $this->typeRepo->findOneBy(['nom' => $type->getNom(), 'application' => $application]);

        $newProduitCategorie->setCategorie($existingCategorie ?: $this->createNewCategorie($categorie, $application));
        $newProduitCategorie->setType($existingType ?: $this->createNewType($type, $application));

        $newProduitCategorie->setNom($oldProduitCategorie->getNom())
            ->setApplication($application)
            ->setReference($oldProduitCategorie->getReference())
            ->setTva($oldProduitCategorie->getTva())
            ->setQtt($oldProduitCategorie->getQtt())
            ->setStockMin($oldProduitCategorie->getStockMin())
            ->setStockMax($oldProduitCategorie->getStockMax())
            ->setUniteVenteGros($oldProduitCategorie->getUniteVenteGros())
            ->setUniteVenteDetail($oldProduitCategorie->getUniteVenteDetail())
            ->setPrixVenteGros($oldProduitCategorie->getPrixVenteGros())
            ->setPrixVenteDetail($oldProduitCategorie->getPrixVenteDetail())
            ->setPrixTTC($oldProduitCategorie->getPrixTTC())
            ->setPrixAchat($oldProduitCategorie->getPrixAchat())
            ->setPrixHt($oldProduitCategorie->getPrixHt())
            ->setDateCreation($date);

        foreach ($oldProduitCategorie->getProductImages() as $productImage) {
            $productImage->setProduitCategorie($newProduitCategorie);
            $productImage->setDateCreation($date);
            $this->entityManager->persist($productImage);
        }

        $stock = new Stock();
        $stock->setProduitCategorie($newProduitCategorie);
        $stock->setQtt($quantity);
        $stock->setDateCreation($date);
        $this->entityManager->persist($stock);

        foreach ($oldProduitCategorie->getComptes() as $compte) {
            $existingCompte = $this->compteRepo->findOneBy(['nom' => $compte->getNom(), 'application' => $application]);
            $newCompte = $existingCompte ?: $this->createNewCompte($compte, $application);
            $newProduitCategorie->addCompte($newCompte);
            $compte->addProduitCategory($newProduitCategorie);
            $this->entityManager->persist($newCompte);
        }

        $this->entityManager->persist($newProduitCategorie);
        $this->update();

        if ($isChangePrice) {
            $newProduitCategorie->setIsChangePrix(true);
            $this->createNotification($application, "Le prix du produit transféré doit être modifié en raison des nouvelles conditions d'application.", $newProduitCategorie);
        }

        // Calcul du stock total
        $totalQtt = 0;
        $stocks = $newProduitCategorie->getStocks();
        foreach ($stocks as $stck) {
            $qtt = $stck->getQtt();
            $totalQtt += $qtt;
        }
        $totalQtt = intval($totalQtt);

        // Calcul du total des transferts
        $totalQttTransfert = 0;
        $transferts = $newProduitCategorie->getTransferts();
        foreach ($transferts as $transfert) {
            $qttTransfert = $transfert->getQuantity();
            $totalQttTransfert += $qttTransfert;
        }
        $totalQttTransfert = intval($totalQttTransfert);

        // Calcul du stock restant final
        $totalQttFinal = $totalQtt - $totalQttTransfert;

        // Mise à jour du stock restant en fonction des conditions
        if (count($stocks) == 0) {
            $newProduitCategorie->setStockRestant($quantity); // Cas où aucun stock existait
        } elseif (count($stocks) > 0 && count($transferts) > 0) {
            $newProduitCategorie->setStockRestant($totalQttFinal); // Cas où il y a des stocks et plusieurs transferts
        } else {
            $newProduitCategorie->setStockRestant($totalQtt); // Cas par défaut
        }

        $this->entityManager->persist($newProduitCategorie);

         // Obtenir l'utilisateur connecté
         $user = $this->security->getUser();

         // Créer log
         $this->logger->info('Produit catégorie transféré', [
             'Produit' => $oldProduitCategorie->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
             'ID Application' => $oldProduitCategorie->getApplication()->getId()
         ]);

        $this->update();

    }

    private function createNewCompte($compte, $application)
    {
        $newCompte = new Compte();
        $newCompte->setNom($compte->getNom());
        $newCompte->setGenre(2);
        $newCompte->setEtat($compte->getEtat() ?: null);
        $newCompte->setStatut($compte->getStatut() ?: null);
        $newCompte->setEmail($compte->getEmail() ?: null);
        $newCompte->setTelephone($compte->getTelephone() ?: null);
        $newCompte->setApplication($application);
        $newCompte->setDateCreation(new \DateTime());

        $this->entityManager->persist($newCompte);
        return $newCompte;
    }

    private function createNewCategorie($categorie, $application)
    {
        $newCategorie = new Categorie();
        $newCategorie->setNom($categorie->getNom());
        $newCategorie->setApplication($application);
        $newCategorie->setDateCreation(new \DateTime());

        $this->entityManager->persist($newCategorie);
        return $newCategorie;
    }

    private function createNewType($type, $application)
    {
        $newType = new ProduitType();
        $newType->setNom($type->getNom());
        $newType->setDescription($type->getDescription() ?: null);
        $newType->setApplication($application);
        $newType->setIsActive(true);
        $newType->setDateCreation(new \DateTime());

        $this->entityManager->persist($newType);
        return $newType;
    }

    private function createNotification($application, $message, $newProduitCategorie)
    {
        $notification = new Notification();
        $notification->setApplication($application);
        $notification->setMessage($message);
        $notification->setDateCreation(new \DateTime());
        $notification->setProduitCategorie($newProduitCategorie);

        $this->entityManager->persist($notification);
        return $notification;
    }


    public function getAllProduitByCompteAndApplication($compte, $application)
    {
        return $this->entityManager->getRepository(ProduitCategorie::class)->findProductsByCompteAndApplication($compte, $application);
    }

    public function getAllProduitDatePeremption()
    {
        return $this->entityManager->getRepository(ProduitCategorie::class)->produitDatePeremptionProche();
    }

    
}
