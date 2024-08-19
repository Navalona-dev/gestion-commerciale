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
    private $application;
    public function __construct(AuthorizationManager $authorization, TokenStorageInterface  $TokenStorageInterface, EntityManagerInterface $entityManager, ApplicationManager  $applicationManager)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
    }

    public function add($instance, $genre)
    {
        $compte = Compte::newCompte($instance);

        $date = new \DateTime();

        $compte->setEtat($instance->getEtat());
        $compte->setApplication($this->application);
        $compte->setDateCreation($date);
        $compte->setStatut($instance->getStatut());
        $compte->setEmail($instance->getEmail());
        $compte->setTelephone($instance->getTelephone());
        $compte->setNbAffaire(0);
        $compte->setAdresse($instance->getAdresse());
        $compte->setIsLivraison(false);
        $compte->setNumero(null);
        //$compte->setCommentaire($instance->getCommentaire());
        //$compte->setCa($instance->getCa());

        if($genre == 1) {
            $compte->setGenre(1);
        } elseif($genre == 2) {
            $compte->setGenre(2);
        }

        /*foreach($compte->getUtilisateur() as $utilisateur) {
            $utilisateur->addCompte($compte);
            $this->entityManager->persist($utilisateur);
        }

        foreach($compte->getCompteApplications() as $compteApplication) {
            $this->entityManager->persist($compteApplication);
        }*/

        $this->entityManager->persist($compte);
        $this->update();
        unset($instance);
        return $compte;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function persist($compte)
    {
        $this->entityManager->persist($compte);
    }

    public function remove($compte)
    {
        $factures = $compte->getFactures();
        foreach($factures as $facture) {
            $factureDetails = $facture->getFactureDetails();
            foreach($factureDetails as $factureDetail) {
                $this->entityManager->remove($factureDetail);
            }
            $this->entityManager->remove($facture);
        }

        $affaires = $compte->getAffaires();

        foreach($affaires as $affaire) {
            $this->entityManager->remove($affaire);
        }

        $this->entityManager->remove($compte);
    }

    public function find($id)
    {
        return $this->entityManager->getRepository(Compte::class)->find($id);
    }

    public function getAllCompte($genre = 1, $start = 1, $limit = 0, $nomCompte = "")
    {
        return $this->entityManager->getRepository(Compte::class)->searchCompte($nomCompte, null,null , $genre, $limit, $start);
    }

    public function searchCompteRawSql($genre, $nom, $dateDu, $dateAu, $etat, $start, $limit, $order, $isCount)
    {
        return $this->entityManager->getRepository(Compte::class)->searchCompteRawSql($genre, $nom, $dateDu,$dateAu, $etat, $limit, $start, $order, $isCount);
    }

    public function getNombreTotalCompte()
    {
        return $this->entityManager->getRepository(Compte::class)->countAll();
    }
}