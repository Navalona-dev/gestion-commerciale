<?php

namespace App\Repository;

use App\Service\DroitService;
use App\Entity\Compte;
use App\Entity\Affaire;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\Constraint\Count;
use App\Entity\Gomyclic\ParcoursEnCours;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Gomyclic\ParcoursIntervenant;
use App\Service\ApplicationManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Cache\InvalidArgumentException;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Affaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Affaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Affaire[]    findAll()
 * @method Affaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffaireRepository extends ServiceEntityRepository
{
    private $application;
    
    private $user;

    public function __construct(
        ManagerRegistry       $registry,
        TokenStorageInterface $tokenStorage,
        ApplicationManager    $applicationManager
    ) {
        parent::__construct($registry, Affaire::class);

        $this->application = $applicationManager->getApplicationActive();
        $this->user = (null == $tokenStorage->getToken()) ? null : $tokenStorage->getToken()->getUser();
        
    }

    public function searchAffaire(
        $compte,
        $dateDu = null,
        $dateAu = null,
        $limit = null,
        $pg = 1,
        $statut = null,
        $nom = null
    ) {
        $query = $this->createQueryBuilder('a')
            //->join('a.application', 'a')
            ->where('a.application = :application')
            ->setParameter('application', $this->application);

        if (null != $nom) {
            $query = $query->andWhere("a.nom LIKE :nom")
                ->setParameter('nom', '%' . $nom . '%');
        }

        if (null != $statut) {
            $query = $query->andWhere("a.statut = :statut")
                ->setParameter('statut', $statut);
        }

        if (null != $compte) {
            $query = $query->andWhere("a.compte = :compte")
                ->setParameter('compte', $compte );
        }

        if (null != $dateDu && null != $dateAu) {
            $query = $query->andWhere('a.dateCreation >= :dateDu AND a.dateCreation <= :dateAu')
                ->setParameter('dateDu', $dateDu->format("Y-m-d"))
                ->setParameter('dateAu', $dateAu->format("Y-m-d"));
        } elseif (null != $dateDu && null == $dateAu) {
            $query = $query->andWhere('a.dateCreation >= :dateDu')
                ->setParameter('dateDu', $dateDu->format("Y-m-d"));
        } elseif (null == $dateDu && null != $dateAu) {
            $query = $query->andWhere('a.dateCreation <= :dateAu')
                ->setParameter('dateAu', $dateAu->format("Y-m-d"));
        }

        if (null != $limit) {
            $query = $query->setMaxResults($limit)
                ->setFirstResult($pg);
        }

        //filtre par etat
        /*if (null != $etat) {

            if ($etat == "pre") {
                $query = $query->andWhere("a.etat LIKE :etat OR a.etat IS NULL");
            } else {
                $query = $query->andWhere("a.etat LIKE :etat");
            }

            $query = $query->setParameter('etat', '%' . $etat . '%');
        }*/

        $tabOrder = [
            0 => 'a.dateCreation',
            1 => 'a.nom',
            2 => 'a.email',
            3 => 'a.telephone',
            4 => 'a.adresse',

        ];
        
 
        $query->orderBy('a.dateCreation', 'DESC');
        
        $result = $query->getQuery()->getResult();
       
        return $result;
    }

    // /**
    //  * @return Affaire[] Returns an array of Affaire objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Affaire
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


     // Nombre de commandes d'aujourd'hui
     public function countAffairesToday($paiement = null, $statut = null)
     {
         $today = new \DateTime();
         $today->setTime(0, 0, 0);
         $tomorrow = clone $today;
         $tomorrow->modify('+1 day');

         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->join('a.factures', 'f')
                     ->where('f.date >= :today')
                     ->andWhere('f.date < :tomorrow')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->andWhere('a.application = :application_id')
                     ->setParameter('today', $today)
                     ->setParameter('tomorrow', $tomorrow)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();

                return $qb;
     }
 
     // Nombre de commandes d'hier
     public function countAffairesYesterday($paiement = null, $statut = null)
     {
         $yesterday = new \DateTime();
         $yesterday->setTime(0, 0, 0);
         $yesterday->modify('-1 day');
         $today = clone $yesterday;
         $today->modify('+1 day');
 
         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->join('a.factures', 'f')
                     ->where('f.date >= :yesterday')
                     ->andWhere('f.date < :today')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->andWhere('a.application = :application_id')
                     ->setParameter('yesterday', $yesterday)
                     ->setParameter('today', $today)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }
 
     // Nombre de commandes cette semaine
     public function countAffairesThisWeek($paiement = null, $statut = null)
     {
         $startOfWeek = new \DateTime();
         $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
         $startOfWeek->setTime(0, 0, 0);
 
         $endOfWeek = clone $startOfWeek;
         $endOfWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->join('a.factures', 'f')
                     ->where('f.date >= :start_of_week')
                     ->andWhere('f.date < :end_of_week')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.application = :application_id')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('start_of_week', $startOfWeek)
                     ->setParameter('end_of_week', $endOfWeek)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }
 
     // Nombre de commandes semaine dernière
     public function countAffairesLastWeek($paiement = null, $statut = null)
     {
         $startOfLastWeek = new \DateTime();
         $startOfLastWeek->setISODate((int)$startOfLastWeek->format('o'), (int)$startOfLastWeek->format('W') - 1);
         $startOfLastWeek->setTime(0, 0, 0);
 
         $endOfLastWeek = clone $startOfLastWeek;
         $endOfLastWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->join('a.factures', 'f')
                     ->where('f.date >= :start_of_last_week')
                     ->andWhere('f.date < :end_of_last_week')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.application = :application_id')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('start_of_last_week', $startOfLastWeek)
                     ->setParameter('end_of_last_week', $endOfLastWeek)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }
 
     // Nombre de commandes ce mois-ci
     public function countAffairesThisMonth($paiement = null, $statut = null)
     {
         $startOfMonth = new \DateTime('first day of this month');
         $startOfMonth->setTime(0, 0, 0);
 
         $endOfMonth = new \DateTime('first day of next month');
         $endOfMonth->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->join('a.factures', 'f')
                     ->where('f.date >= :start_of_month')
                     ->andWhere('f.date < :end_of_month')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->andWhere('a.application = :application_id')
                     ->setParameter('start_of_month', $startOfMonth)
                     ->setParameter('end_of_month', $endOfMonth)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }
 
     // Nombre de commandes mois dernier
     public function countAffairesLastMonth()
     {
         $startOfLastMonth = new \DateTime('first day of last month');
         $startOfLastMonth->setTime(0, 0, 0);
 
         $endOfLastMonth = new \DateTime('first day of this month');
         $endOfLastMonth->setTime(0, 0, 0);
 
         return $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->where('a.createdAt >= :start_of_last_month')
                     ->andWhere('a.createdAt < :end_of_last_month')
                     ->andWhere('a.isPaid = :isPaid')
                     ->setParameter('start_of_last_month', $startOfLastMonth)
                     ->setParameter('end_of_last_month', $endOfLastMonth)
                     ->setParameter('isPaid', true)
                     ->getQuery()
                     ->getSingleScalarResult();
     }
 
     // Nombre de commandes cette année
     public function countAffairesThisYear()
     {
         $startOfYear = new \DateTime('first day of January this year');
         $startOfYear->setTime(0, 0, 0);
 
         $endOfYear = new \DateTime('first day of January next year');
         $endOfYear->setTime(0, 0, 0);
 
         return $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->where('a.createdAt >= :start_of_year')
                     ->andWhere('a.createdAt < :end_of_year')
                     ->andWhere('a.isPaid = :isPaid')
                     ->setParameter('start_of_year', $startOfYear)
                     ->setParameter('end_of_year', $endOfYear)
                     ->setParameter('isPaid', true)
                     ->getQuery()
                     ->getSingleScalarResult();
     }
 
     // Nombre de commandes année dernière
     public function countAffairesLastYear()
     {
         $startOfLastYear = new \DateTime('first day of January last year');
         $startOfLastYear->setTime(0, 0, 0);
 
         $endOfLastYear = new \DateTime('first day of January this year');
         $endOfLastYear->setTime(0, 0, 0);
 
         return $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->where('a.createdAt >= :start_of_last_year')
                     ->andWhere('a.createdAt < :end_of_last_year')
                     ->andWhere('a.isPaid = :isPaid')
                     ->setParameter('start_of_last_year', $startOfLastYear)
                     ->setParameter('end_of_last_year', $endOfLastYear)
                     ->setParameter('isPaid', true)
                     ->getQuery()
                     ->getSingleScalarResult();
     }
}
