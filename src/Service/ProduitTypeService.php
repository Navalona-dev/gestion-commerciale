<?php
namespace App\Service;

use App\Entity\Categorie;
use App\Entity\ProduitType;
use Doctrine\ORM\EntityManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProduitTypeService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $session;
    public  $isCurrentDossier = false;
    private $application;

    public function __construct(AuthorizationManager $authorization, TokenStorageInterface  $TokenStorageInterface, EntityManagerInterface $entityManager, ApplicationManager  $applicationManager)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
    }

    public function add($instance)
    {
        $produitType = ProduitType::newProduitType($instance);

        $date = new \DateTime();

        $produitType->setApplication($this->application);
        $produitType->setDateCreation($date);
        $produitType->setIsActive($instance->getIsActive());
        $produitType->setDescription($instance->getDescription());

        $this->entityManager->persist($produitType);
        $this->update();
        unset($instance);
        return $produitType;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($produitType)
    {
        $this->entityManager->remove($produitType);
        $this->update();
    }

    public function getAllProduitTypes()
    {
        $produitTypes = $this->entityManager->getRepository(ProduitType::class)->getAllTypes();
        if ($produitTypes != false && count($produitTypes) > 0) {
            return $produitTypes;
        }
        return false;
    }

    public function getProduitTypeById($id)
    {
        $produitType = $this->entityManager->getRepository(ProduitType::class)->find($id);
        if ($produitType) {
            return $produitType;
        }
        return null;
    }

}