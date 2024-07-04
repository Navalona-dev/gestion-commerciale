<?php
namespace App\Service;

use App\Entity\Compte;
use App\Entity\Stock;
use Doctrine\ORM\EntityManager;
use App\Entity\ProduitCategorie;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
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

    public function __construct(AuthorizationManager $authorization, TokenStorageInterface  $TokenStorageInterface, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
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
        $produitCategorie->setStockMin($instance->getStockMin());
        $produitCategorie->setStockMax($instance->getStockMax());
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
        $this->update();
    }

    public function getAllProduitCategories()
    {
        $produitCategories = $this->entityManager->getRepository(ProduitCategorie::class)->getProduits();
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

        $stocks = $oldProduitCategorie->getStocks();
        $remainingQuantity = $quantity;
        
        // Parcourir les stocks en commençant par le dernier
        for ($i = count($stocks) - 1; $i >= 0; $i--) {
            $stock = $stocks[$i];
            $stockQuantity = $stock->getQtt();
            
            if ($stockQuantity >= $remainingQuantity) {
                $qtt = $stockQuantity - $remainingQuantity;
                if($qtt < 0) {
                    $stock->setQtt(0);
                } else {
                    $stock->setQtt($qtt);
                }

                $this->entityManager->persist($stock);
                break;

            } else {
                $remainingQuantity -= $stockQuantity;
                $stock->setQtt(0);
                $this->entityManager->persist($stock);
            }
        }
    }

    public function updateStockNewApplication($productReferenceExists, $quantity)
    {
        $oldStockRestantNewApp = $productReferenceExists->getStockRestant();
        $newStockRestantNewApp = $oldStockRestantNewApp + $quantity;
        $productReferenceExists->setStockRestant($newStockRestantNewApp);

        $date = new \DateTime();

        $stock = new Stock();
        $stock->setProduitCategorie($productReferenceExists);
        $stock->setQtt($quantity);
        $stock->setDateCreation($date);

        $this->entityManager->persist($stock);

        $this->entityManager->persist($productReferenceExists);
    }

    public function addNewProductForNewApplication($oldProduitCategorie, $quantity, $application)
    {
        $productReferenceExists = new ProduitCategorie();

        $date = new \DateTime();

        $productReferenceExists->setNom($oldProduitCategorie->getNom())
                            ->setApplication($application)
                            ->setReference($oldProduitCategorie->getReference())
                            ->setCategorie($oldProduitCategorie->getCategorie())
                            ->setType($oldProduitCategorie->getType())
                            ->setTva($oldProduitCategorie->getTva())
                            ->setQtt($oldProduitCategorie->getQtt())
                            ->setStockRestant($quantity)
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
                            $productImage->setProduitCategorie($productReferenceExists);
                            $productImage->setDateCreation($date);
                            $this->entityManager->persist($productImage);
                        }

                        $stock = new Stock();
                        $stock->setProduitCategorie($productReferenceExists);
                        $stock->setQtt($quantity);
                        $stock->setDateCreation($date);

                        $this->entityManager->persist($stock);
                        $this->entityManager->persist($productReferenceExists);

    }

}
