<?php
namespace App\Service;

use App\Entity\Affaire;
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

class AffaireService
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

    public function add($instance, $statut, $compte = null)
    {
        $affaire = Affaire::newAffaire($instance, $statut, $compte);

        //$affaire->setEtat($instance->getEtat());
        $affaire->setApplication($this->application);
        $affaire->setPrestation("Vente");
        $affaire->setNumero(null);
        
        /*foreach($affaire->getUtilisateur() as $utilisateur) {
            $utilisateur->addCompte($affaire);
            $this->entityManager->persist($utilisateur);
        }

        foreach($affaire->getCompteApplications() as $affaireApplication) {
            $this->entityManager->persist($affaireApplication);
        }*/

        $this->entityManager->persist($affaire);
        $this->update();
        unset($instance);
        return $affaire;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function persist($affaire)
    {
        $this->entityManager->persist($affaire);
    }

    public function remove($affaire)
    {
        $this->entityManager->remove($affaire);
    }

    public function find($id)
    {
        return $this->entityManager->getRepository(Affaire::class)->find($id);
    }

    public function getAllAffaire($compte = null, $start = 1, $limit = 0, $statut = null)
    {
        return $this->entityManager->getRepository(Affaire::class)->searchAffaire($compte, null,null, $limit, $start, $statut);
    }

    public function searchCompteRawSql($genre, $nom, $dateDu, $dateAu, $etat, $start, $limit, $order, $isCount)
    {
        return $this->entityManager->getRepository(Affaire::class)->searchCompteRawSql($genre, $nom, $dateDu,$dateAu, $etat, $limit, $start, $order, $isCount);
    }

    public function getNombreTotalCompte()
    {
        return $this->entityManager->getRepository(Affaire::class)->countAll();
    }
}