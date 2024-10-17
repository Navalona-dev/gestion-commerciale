<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use App\Entity\Facture;
use App\Entity\Benefice;
use App\Entity\FactureBenefice;
use App\Service\ApplicationManager;
use App\Repository\FactureRepository;
use App\Service\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FourchetteService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $application;
   

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        ApplicationManager  $applicationManager,

    )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();

    }

    public function add($fourchette = null) {
        $fourchette->setDateCreation(new \DateTime());
        $fourchette->setApplication($this->application);
        $this->entityManager->persist($fourchette);
        $this->update();
        return $fourchette;
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