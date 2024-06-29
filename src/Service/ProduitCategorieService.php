<?php
namespace App\Service;

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

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($produitCategorie)
    {
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
        return null;
    }

}