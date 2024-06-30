<?php
namespace App\Service;

use App\Entity\ProductImage;
use Doctrine\ORM\EntityManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProduitImageService
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

    public function add($instance, $produitCategorie)
    {
        $produitImage = ProductImage::newProduitImage($instance);

        $date = new \DateTime();

        $produitImage->setDateCreation($date);
        $produitImage->setProduitCategorie($produitCategorie);

        $this->entityManager->persist($produitImage);

        $this->update();
        unset($instance);
        return $produitImage;
    }


    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($produitImage)
    {
        $this->entityManager->remove($produitImage);

        $this->update();
    }

    public function getAllProduitImages()
    {
        $produitImages = $this->entityManager->getRepository(ProductImage::class)->findAll();
        if (count($produitImages) > 0) {
            return $produitImages;
        }
        return false;
    }

    public function getProduitImageById($id)
    {
        $produitImage = $this->entityManager->getRepository(ProductImage::class)->find($id);
        if ($produitImage) {
            return $produitImage;
        }
        return null;
    }

    public function getImageByProduit($produitCategorie)
    {
        $produitImage = $this->entityManager->getRepository(ProductImage::class)->findByProductCategory($produitCategorie);
        if ($produitImage) {
            return $produitImage;
        }
        return null;
    }

}