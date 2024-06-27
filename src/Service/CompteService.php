<?php
namespace App\Service;

use App\Entity\Compte;
use App\Service\AuthorizationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Exception\PropertyVideException;
use App\Exception\ActionInvalideException;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CompteService
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

    public function add($compte)
    {
        $this->entityManager->persist($compte);
        return $compte;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($compte)
    {
        $this->entityManager->remove($compte);
    }

    public function getAllCompte($genre = 1, $start = 0, $limit = 0, $search = "")
    {
        return $this->entityManager->getRepository(Compte::class)->searchCompte(null, null,null , $genre, $limit, $start, $search);
    }

    public function getNombreTotalCompte()
    {
        return $this->entityManager->getRepository(Compte::class)->countAll();
    }
}