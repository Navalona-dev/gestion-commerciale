<?php
namespace App\Service;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Affaire;
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
//use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AffaireService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $session;
    public  $isCurrentDossier = false;
    private $application;
    private $logger;
    private $security;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager, 
        ApplicationManager  $applicationManager,
        LoggerInterface $affaireLogger, 
        Security $security
        )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
        $this->logger = $affaireLogger;
        $this->security = $security;
    }

    public function add($instance, $statut, $compte = null, $application = null, $depot = null)
    {
        $affaire = Affaire::newAffaire($instance, $statut, $compte);

        //$affaire->setEtat($instance->getEtat());
        $affaire->setApplication($this->application);
        $affaire->setPrestation("Vente");
        $affaire->setNumero(null);
        if($statut == "commande" && $application != null) {
            $affaire->setApplicationRevendeur($application);
        }

        if($statut == "commande" && $depot == "on") {
            $affaire->setDepot(true);
        }
        
        /*foreach($affaire->getUtilisateur() as $utilisateur) {
            $utilisateur->addCompte($affaire);
            $this->entityManager->persist($utilisateur);
        }

        foreach($affaire->getCompteApplications() as $affaireApplication) {
            $this->entityManager->persist($affaireApplication);
        }*/

        $this->entityManager->persist($affaire);

          // Obtenir l'utilisateur connecté
          $user = $this->security->getUser();

          // Créer le message de log en fonction de l'action
          $logMessage = ($affaire->getStatut() == 'devis') ? 'Devis ajouté' : 'Commande ajoutée';
  
          // Créer le log
          $this->logger->info($logMessage, [
            'Commande' => $affaire->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $affaire->getApplication()->getId()
        ]);

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
        $factures = $affaire->getFactures();
        foreach($factures as $facture) {
            $factureDetails = $facture->getFactureDetails();
            foreach($factureDetails as $factureDetail) {
                $this->entityManager->remove($factureDetail);
            }

            $factureEcheances = $facture->getFactureEcheances();
            foreach($factureEcheances as $factureEcheance) {
                $this->entityManager->remove($factureEcheance);
            }

            $this->entityManager->remove($facture);

            
        }
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

    public function getAffaires($statut = null)
    {
        return $this->entityManager->getRepository(Affaire::class)->getAllAffaires($statut);
    }
}