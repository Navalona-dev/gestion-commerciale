<?php
namespace App\Service;

use App\Entity\ProductImage;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
use Symfony\Component\Security\Core\Security;
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
    private $logger;
    private $security;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        LoggerInterface $productLogger, 
        Security $security)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->logger = $productLogger;
        $this->security = $security;
    }

    public function add($instance, $produitCategorie, $isUpdate = false)
    {
        $produitImage = ProductImage::newProduitImage($instance);

        $date = new \DateTime();

        $produitImage->setDateCreation($date);
        $produitImage->setProduitCategorie($produitCategorie);

        $this->entityManager->persist($produitImage);

          // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer le message de log en fonction de l'action
        $logMessage = $isUpdate ? 'Image de produit catégorie modifiée' : 'Image de produit catégorie ajoutée';

        // Créer le log
        $this->logger->info($logMessage, [
            'Produit' => $produitCategorie->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $produitCategorie->getApplication()->getId()
        ]);

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

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer log
        $this->logger->info('Image de produit catégorie supprimée', [
            'Produit' => $produitImage->getProduitCategorie()->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $produitImage->getProduitCategorie()->getApplication()->getId()
        ]);

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
        return false;
    }

    public function getImageByProduit($produitCategorie)
    {
        $produitImage = $this->entityManager->getRepository(ProductImage::class)->findByProductCategory($produitCategorie);
        if ($produitImage) {
            return $produitImage;
        }
        return false;
    }

}