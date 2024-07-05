<?php
namespace App\Service;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CategorieService
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
        $categorie = Categorie::newCategorie($instance);

        $date = new \DateTime();

        $categorie->setEtat($instance->getEtat());
        $categorie->setApplication($this->application);
        $categorie->setDateCreation($date);
        $categorie->setStock($instance->getStock());

        $this->entityManager->persist($categorie);
        $this->update();
        unset($instance);
        return $categorie;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($categorie)
    {
        $this->entityManager->remove($categorie);
        $this->update();
    }

    public function getAllCategories()
    {
        $categories = $this->entityManager->getRepository(Categorie::class)->getAllCategories();
        if ($categories != false && count($categories) > 0) {
            return $categories;
        }
        return false;
    }

    public function getCategorieById($id)
    {
        $categorie = $this->entityManager->getRepository(Categorie::class)->find($id);
        if ($categorie) {
            return $categorie;
        }
        return null;
    }

}