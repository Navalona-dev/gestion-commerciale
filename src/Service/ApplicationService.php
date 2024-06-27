<?php
namespace App\Service;

use App\Service\AuthorizationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Exception\PropertyVideException;
use App\Exception\ActionInvalideException;
use App\Entity\application;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class ApplicationService
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
        $application = Application::newApplicationFromInstance($instance);
        
        $this->entityManager->persist($application);
        $this->update();
        unset($instance);
        return $application;
    }

    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($application)
    {
        $this->entityManager->remove($application);
        $this->update();
    }

    public function getAllapplications()
    {
        $applications = $this->entityManager->getRepository(Application::class)->findAll();
        if (count($applications) > 0) {
            return $applications;
        }
        return false;
    }

    public function getAllApplicationByCategorie($categorie)
    {
        $applications = $this->entityManager->getRepository(Application::class)->findBy(['categoryofapplication' => $categorie]);
        if (count($applications) > 0) {
            return $applications;
        }
        return false;
    }

    public function getApplicationById($id)
    {
        $application = $this->entityManager->getRepository(Application::class)->find($id);
        if ($application) {
            return $application;
        }
        return null;
    }

    public function addPrivilege($application, $privilege)
    {
        $application->addPrivilege($privilege);
    }

    public function removePrivilege($privilege, $application)
    {
        $application->removePrivilege($privilege);
    }

}