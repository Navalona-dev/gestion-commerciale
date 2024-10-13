<?php
namespace App\Service;

use App\Service\ApplicationManager;
use App\Service\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DepenseService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $application;


    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        ApplicationManager  $applicationManager
    )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();

    }

    public function add($depense = null)
    {
        $depense->setDateCreation(new \DateTime);
        $depense->setApplication($this->application);
        $this->entityManager->persist($depense);
        $this->entityManager->flush();

        return $depense;
    }

    public function remove($entity)
    {
        $this->entityManager->remove($entity);
        return $entity;
    }

    public function update()
    {
        $this->entityManager->flush();
    }
}