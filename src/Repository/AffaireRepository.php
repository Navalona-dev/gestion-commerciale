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

    public function getAllAffaires($statut = null)
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.nom as nom_affaire, a.dateCommande, a.dateDevis, a.statut, a.paiement, a.dateFacture, a.dateAnnule, c.nom as nom_compte, a.dateCreation, p.nom as nom_produit, p.reference, p.puHt, p.qtt, p.prixAchat, p.typeVente, p.prixVenteGros, p.prixVenteDetail')
            ->leftJoin('a.compte', 'c')
            ->leftJoin('a.products', 'p')
            ->innerJoin('a.application', 'app')
            ->andWhere('app.id = :application')
            ->setParameter('application', $this->application)
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('a.dateCreation', 'ASC')
            ->getQuery()
            ->getArrayResult();
    
        $affaires = [];
        foreach ($results as $row) {
            $affaireId = $row['nom_affaire'];
    
            if (!isset($affaires[$affaireId])) {
                $affaires[$affaireId] = [
                    'nom_affaire' => $row['nom_affaire'],
                    'dateCreation' => $row['dateCreation'],
                    'dateDevis' => $row['dateDevis'],
                    'dateCommande' => $row['dateCommande'],
                    'dateFacture' => $row['dateFacture'],
                    'dateAnnule' => $row['dateAnnule'],
                    'nom_compte' => $row['nom_compte'],
                    'paiement' => $row['paiement'],
                    'produits' => []
                ];
            }
    
            $affaires[$affaireId]['produits'][] = [
                'nom_produit' => $row['nom_produit'],
                'reference' => $row['reference'],
                'qtt' => $row['qtt'],
                'prixVenteGros' => $row['prixVenteGros'],
                'prixVenteDetail' => $row['prixVenteDetail'],
                'typeVente' => $row['typeVente']
            ];
        }
    
        return $affaires;
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
                     ->where('a.dateFacture >= :today')
                     ->andWhere('a.dateFacture < :tomorrow')
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
                     ->where('a.dateFacture >= :yesterday')
                     ->andWhere('a.dateFacture < :today')
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
                     ->where('a.dateFacture >= :start_of_week')
                     ->andWhere('a.dateFacture < :end_of_week')
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
                     ->where('a.dateFacture >= :start_of_last_week')
                     ->andWhere('a.dateFacture < :end_of_last_week')
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
                     ->where('a.dateFacture >= :start_of_month')
                     ->andWhere('a.dateFacture < :end_of_month')
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
     public function countAffairesLastMonth($paiement = null, $statut = null)
     {
         $startOfLastMonth = new \DateTime('first day of last month');
         $startOfLastMonth->setTime(0, 0, 0);
 
         $endOfLastMonth = new \DateTime('first day of this month');
         $endOfLastMonth->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->where('a.dateFacture >= :start_of_last_month')
                     ->andWhere('a.dateFacture < :end_of_last_month')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->andWhere('a.application = :application_id')
                     ->setParameter('start_of_last_month', $startOfLastMonth)
                     ->setParameter('end_of_last_month', $endOfLastMonth)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }
 
     // Nombre de commandes cette année
     public function countAffairesThisYear($paiement = null, $statut = null)
     {
         $startOfYear = new \DateTime('first day of January this year');
         $startOfYear->setTime(0, 0, 0);
 
         $endOfYear = new \DateTime('first day of January next year');
         $endOfYear->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->where('a.dateFacture >= :start_of_year')
                     ->andWhere('a.dateFacture < :end_of_year')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->andWhere('a.application = :application_id')
                     ->setParameter('start_of_year', $startOfYear)
                     ->setParameter('end_of_year', $endOfYear)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }
 
     // Nombre de commandes année dernière
     public function countAffairesLastYear($paiement = null, $statut = null)
     {
         $startOfLastYear = new \DateTime('first day of January last year');
         $startOfLastYear->setTime(0, 0, 0);
 
         $endOfLastYear = new \DateTime('first day of January this year');
         $endOfLastYear->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('a')
                     ->select('COUNT(a.id)')
                     ->where('a.dateFacture >= :start_of_last_year')
                     ->andWhere('a.dateFacture < :end_of_last_year')
                     ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->andWhere('a.application = :application_id')
                     ->setParameter('start_of_last_year', $startOfLastYear)
                     ->setParameter('end_of_last_year', $endOfLastYear)
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

        // Nombre de stock vendu d'aujourd'hui
      public function countStocksVenduToday($paiement = null, $statut = null)
      {
          $today = new \DateTime();
          $today->setTime(0, 0, 0);
          $tomorrow = clone $today;
          $tomorrow->modify('+1 day');
  
          $qb = $this->createQueryBuilder('a')
                      ->select('SUM(p.qtt)')
                      ->join('a.products', 'p')
                      ->where('a.dateFacture >= :today')
                      ->andWhere('a.dateFacture < :tomorrow')
                      ->andWhere('a.application = :application_id')
                      ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                      ->setParameter('today', $today)
                      ->setParameter('tomorrow', $tomorrow)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
  
                 return $qb;
      }
  
    // Nombre de stock vendu d'hier
    public function countStocksVenduYesterday($paiement = null, $statut = null)
    {
        $yesterday = new \DateTime();
        $yesterday->setTime(0, 0, 0);
        $yesterday->modify('-1 day');
        $today = clone $yesterday;
        $today->modify('+1 day');

        $qb = $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt)')
                    ->join('a.products', 'p')
                    ->where('a.dateFacture >= :yesterday')
                    ->andWhere('a.dateFacture < :today')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                    ->setParameter('yesterday', $yesterday)
                    ->setParameter('today', $today)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    // Nombre de stock vendu cette semaine
    public function countStockVenduThisWeek($paiement = null, $statut = null)
    {
        $startOfWeek = new \DateTime();
        $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
        $startOfWeek->setTime(0, 0, 0);

        $endOfWeek = clone $startOfWeek;
        $endOfWeek->modify('+7 days');

        $qb = $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt)')
                    ->join('a.products', 'p')
                    ->where('a.dateFacture >= :start_of_week')
                    ->andWhere('a.dateFacture < :end_of_week')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                    ->setParameter('start_of_week', $startOfWeek)
                    ->setParameter('end_of_week', $endOfWeek)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    // Nombre de stock vendu semaine dernière
    public function countStocksVenduLastWeek($paiement = null, $statut = null)
    {
        $startOfLastWeek = new \DateTime();
        $startOfLastWeek->setISODate((int)$startOfLastWeek->format('o'), (int)$startOfLastWeek->format('W') - 1);
        $startOfLastWeek->setTime(0, 0, 0);

        $endOfLastWeek = clone $startOfLastWeek;
        $endOfLastWeek->modify('+7 days');

        $qb = $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt)')
                    ->join('a.products', 'p')
                    ->where('a.dateFacture >= :start_of_last_week')
                    ->andWhere('a.dateFacture < :end_of_last_week')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                    ->setParameter('start_of_last_week', $startOfLastWeek)
                    ->setParameter('end_of_last_week', $endOfLastWeek)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    // Nombre de stock vendu ce mois-ci
    public function countStocksVenduThisMonth($paiement = null, $statut = null)
    {
        $startOfMonth = new \DateTime('first day of this month');
        $startOfMonth->setTime(0, 0, 0);

        $endOfMonth = new \DateTime('first day of next month');
        $endOfMonth->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt)')
                    ->join('a.products', 'p')
                    ->where('a.dateFacture >= :start_of_month')
                    ->andWhere('a.dateFacture < :end_of_month')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                    ->setParameter('start_of_month', $startOfMonth)
                    ->setParameter('end_of_month', $endOfMonth)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    // Nombre de stock vendu mois dernier
    public function countStocksVenduLastMonth($paiement = null, $statut = null)
    {
        $startOfLastMonth = new \DateTime('first day of last month');
        $startOfLastMonth->setTime(0, 0, 0);

        $endOfLastMonth = new \DateTime('first day of this month');
        $endOfLastMonth->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt)')
                    ->join('a.products', 'p')
                    ->where('a.dateFacture >= :start_of_last_month')
                    ->andWhere('a.dateFacture < :end_of_last_month')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                    ->setParameter('start_of_last_month', $startOfLastMonth)
                    ->setParameter('end_of_last_month', $endOfLastMonth)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    // Nombre de stock vendu cette année
    public function countStocksVenduThisYear($paiement = null, $statut = null)
    {
        $startOfYear = new \DateTime('first day of January this year');
        $startOfYear->setTime(0, 0, 0);

        $endOfYear = new \DateTime('first day of January next year');
        $endOfYear->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt)')
                    ->join('a.products', 'p')
                    ->where('a.dateFacture >= :start_of_year')
                    ->andWhere('a.dateFacture < :end_of_year')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                    ->setParameter('start_of_year', $startOfYear)
                    ->setParameter('end_of_year', $endOfYear)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    // Nombre de stock vendu année dernière
    public function countStocksVenduLastYear($paiement = null, $statut = null)
    {
        $startOfLastYear = new \DateTime('first day of January last year');
        $startOfLastYear->setTime(0, 0, 0);

        $endOfLastYear = new \DateTime('first day of January this year');
        $endOfLastYear->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt)')
                    ->join('a.products', 'p')
                    ->where('a.dateFacture >= :start_of_last_year')
                    ->andWhere('a.dateFacture < :end_of_last_year')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.paiement = :paiement')
                     ->andWhere('a.statut = :statut')
                     ->setParameter('paiement', $paiement)
                     ->setParameter('statut', $statut)
                    ->setParameter('start_of_last_year', $startOfLastYear)
                    ->setParameter('end_of_last_year', $endOfLastYear)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    //nombre de commande de cette semaine par jour
    public function countOrdersThisWeekByDay(\DateTime $day, $paiement = null, $statut = null)
    {
        $startOfDay = clone $day;
        $startOfDay->setTime(0, 0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        return $this->createQueryBuilder('a')
                    ->select('COUNT(a.id)')
                    ->where('a.dateCreation >= :start_of_day')
                    ->andWhere('a.dateCreation < :end_of_day')
                    ->andWhere('a.paiement = :paiement')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.statut = :statut')
                    ->setParameter('start_of_day', $startOfDay)
                    ->setParameter('end_of_day', $endOfDay)
                    ->setParameter('paiement', $paiement)
                    ->setParameter('statut', $statut)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function countOrdersLastWeekByDay($dayOfWeek, $paiement = null, $statut = null)
    {
        $currentDate = new \DateTime();
        $startOfLastWeek = new \DateTime();
        $startOfLastWeek->setISODate((int)$currentDate->format('o'), (int)$currentDate->format('W') - 1);
        $startOfLastWeek->setTime(0, 0, 0);
        
        // Calcule le jour spécifique de la semaine dernière
        $specificDayOfLastWeek = clone $startOfLastWeek;
        $specificDayOfLastWeek->modify('+' . ($dayOfWeek - 1) . ' days');
    
        $startOfDay = clone $specificDayOfLastWeek;
        $startOfDay->setTime(0, 0, 0);
    
        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');
    
        return $this->createQueryBuilder('a')
                    ->select('COUNT(a.id)')
                    ->where('a.dateCreation >= :start_of_day')
                    ->andWhere('a.dateCreation < :end_of_day')
                    ->andWhere('a.paiement = :paiement')
                    ->andWhere('a.application = :application_id')
                    ->andWhere('a.statut = :statut')
                    ->setParameter('start_of_day', $startOfDay)
                    ->setParameter('end_of_day', $endOfDay)
                    ->setParameter('paiement', $paiement)
                    ->setParameter('statut', $statut)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    // Commandes par mois cette année
    public function countOrdersThisYearByMonth($year, $month, $paiement = null, $statut = null)
    {
        $startDate = new \DateTime("$year-$month-01 00:00:00");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.dateCreation BETWEEN :start_date AND :end_date')
            ->andWhere('a.paiement = :paiement')
            ->andWhere('a.application = :application_id')
            ->andWhere('a.statut = :statut')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->setParameter('paiement', $paiement)
            ->setParameter('statut', $statut)
            ->setParameter('application_id', $this->application->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Commandes par mois l'année dernière
    public function countOrdersLastYearByMonth($year, $month, $paiement = null, $statut = null)
    {
        $startDate = new \DateTime("$year-$month-01 00:00:00");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.dateCreation BETWEEN :start_date AND :end_date')
            ->andWhere('a.paiement = :paiement')
            ->andWhere('a.application = :application_id')
            ->andWhere('a.statut = :statut')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->setParameter('paiement', $paiement)
            ->setParameter('statut', $statut)
            ->setParameter('application_id', $this->application->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countProductsSoldThisWeekByDay($dayOfWeek, $paiement = null, $statut = null)
    {
        $currentDate = new \DateTime();
        $startOfWeek = new \DateTime();
        $startOfWeek->setISODate((int)$currentDate->format('o'), (int)$currentDate->format('W'));
        $startOfWeek->setTime(0, 0, 0);
    
        // Calcule le jour spécifique de cette semaine
        $specificDayOfWeek = clone $startOfWeek;
        $specificDayOfWeek->modify('+' . ($dayOfWeek - 1) . ' days');
    
        $startOfDay = clone $specificDayOfWeek;
        $startOfDay->setTime(0, 0, 0);
    
        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');
    
        return $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt) as totalOrderedProducts')
                    ->join('a.products', 'p')
                    ->where('a.dateCreation >= :start_of_day')
                    ->andWhere('a.dateCreation < :end_of_day')
                    ->andWhere('a.paiement = :paiement')
                    ->andWhere('a.statut = :statut')
                    ->andWhere('a.application = :application_id')
                    ->setParameter('start_of_day', $startOfDay)
                    ->setParameter('end_of_day', $endOfDay)
                    ->setParameter('paiement', $paiement)
                    ->setParameter('statut', $statut)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    
    public function countProductsSoldLastWeekByDay($dayOfWeek, $paiement = null, $statut = null)
    {
        $currentDate = new \DateTime();
        $startOfLastWeek = new \DateTime();
        $startOfLastWeek->setISODate((int)$currentDate->format('o'), (int)$currentDate->format('W') - 1);
        $startOfLastWeek->setTime(0, 0, 0);

        // Calcule le jour spécifique de la semaine dernière
        $specificDayOfLastWeek = clone $startOfLastWeek;
        $specificDayOfLastWeek->modify('+' . ($dayOfWeek - 1) . ' days');

        $startOfDay = clone $specificDayOfLastWeek;
        $startOfDay->setTime(0, 0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        return $this->createQueryBuilder('a')
                    ->select('SUM(p.qtt) as totalOrderedProducts')
                    ->join('a.products', 'p')
                    ->where('a.dateCreation >= :start_of_day')
                    ->andWhere('a.dateCreation < :end_of_day')
                    ->andWhere('a.paiement = :paiement')
                    ->andWhere('a.statut = :statut')
                    ->andWhere('a.application = :application_id')
                    ->setParameter('start_of_day', $startOfDay)
                    ->setParameter('end_of_day', $endOfDay)
                    ->setParameter('paiement', $paiement)
                    ->setParameter('statut', $statut)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
    }

     // Produits vendus par mois cette année
     public function countProductsSoldThisYearByMonth($year, $month, $paiement = null, $statut = null)
     {
         $startDate = new \DateTime("$year-$month-01 00:00:00");
         $endDate = clone $startDate;
         $endDate->modify('last day of this month')->setTime(23, 59, 59);
 
         return $this->createQueryBuilder('a')
             ->select('SUM(p.qtt) as totalOrderedProducts')
             ->join('a.products', 'p')
             ->where('a.dateCreation BETWEEN :start_date AND :end_date')
             ->andWhere('a.paiement = :paiement')
            ->andWhere('a.statut = :statut')
            ->andWhere('a.application = :application_id')
             ->setParameter('start_date', $startDate)
             ->setParameter('end_date', $endDate)
             ->setParameter('paiement', $paiement)
            ->setParameter('statut', $statut)
            ->setParameter('application_id', $this->application->getId())
             ->getQuery()
             ->getSingleScalarResult();
     }
 
     // Produits vendus par mois l'année dernière
     public function countProductsSoldLastYearByMonth($year, $month, $paiement = null, $statut = null)
     {
         $startDate = new \DateTime("$year-$month-01 00:00:00");
         $endDate = clone $startDate;
         $endDate->modify('last day of this month')->setTime(23, 59, 59);
 
         return $this->createQueryBuilder('a')
             ->select('SUM(p.qtt) as totalOrderedProducts')
             ->join('a.products', 'p')
             ->where('a.dateCreation BETWEEN :start_date AND :end_date')
             ->andWhere('a.paiement = :paiement')
            ->andWhere('a.statut = :statut')
            ->andWhere('a.application = :application_id')
             ->setParameter('start_date', $startDate)
             ->setParameter('end_date', $endDate)
             ->setParameter('paiement', $paiement)
            ->setParameter('statut', $statut)
            ->setParameter('application_id', $this->application->getId())
             ->getQuery()
             ->getSingleScalarResult();
     }

     //best order

     public function getTopOrdersByTotal($startDate = null, $endDate = null, $paiement = null, $statut = null)
     {
         $qb = $this->createQueryBuilder('a')
             ->select('a as order, SUM(p.puHt) as total, c.nom as nomCompte')
             ->join('a.products', 'p')
             ->join('a.compte', 'c')
             ->where('a.dateFacture >= :startDate')
             ->andWhere('a.dateFacture < :endDate')
             ->andWhere('a.paiement = :paiement')
             ->andWhere('a.statut = :statut')
             ->andWhere('a.application = :application_id')
             ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
             ->setParameter('endDate', $endDate->format('Y-m-d H:i:s')) // Utiliser 23:59:59 pour inclure toute la journée
             ->setParameter('paiement', $paiement)
             ->setParameter('statut', $statut)
             ->setParameter('application_id', $this->application->getId())
             ->groupBy('a.id') // Grouper par ID d'affaire, ce qui est plus sûr
             ->orderBy('total', 'DESC')
             ->setMaxResults(5)
             ->getQuery();
     
         return $qb->getResult();
     }
     


    public function getTopOrdersByTotalToday($paiement = null, $statut = null)
    {
        $today = new \DateTime('today');
        $endOfToday = (clone $today)->modify('+1 day')->setTime(0, 0, 0);
        return $this->getTopOrdersByTotal($today, $endOfToday, $paiement, $statut);
    }
    
    public function getTopOrdersByTotalYesterday($paiement = null, $statut = null)
    {
        $yesterday = new \DateTime('yesterday');
        $startOfYesterday = $yesterday->setTime(0, 0, 0);
        $endOfYesterday = (clone $yesterday)->modify('+1 day')->setTime(0, 0, 0); 

        return $this->getTopOrdersByTotal($startOfYesterday, $endOfYesterday, $paiement, $statut);
    }

    

    public function getTopOrdersByTotalThisWeek($paiement = null, $statut = null) 
    {
        $startOfWeek = new \DateTime('monday this week');
        $endOfWeek = new \DateTime('sunday this week');
        return $this->getTopOrdersByTotal($startOfWeek, $endOfWeek->modify('+1 day'), $paiement, $statut);
    }

    public function getTopOrdersByTotalLastWeek($paiement = null, $statut = null) 
    {
        $startOfLastWeek = new \DateTime('monday last week');
        $endOfLastWeek = new \DateTime('sunday last week');
        return $this->getTopOrdersByTotal($startOfLastWeek, $endOfLastWeek->modify('+1 day'), $paiement, $statut);
    }

    public function getTopOrdersByTotalThisMonth($paiement = null, $statut = null)
    {
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month');
        return $this->getTopOrdersByTotal($startOfMonth, $endOfMonth->modify('+1 day'), $paiement, $statut);
    }

    public function getTopOrdersByTotalLastMonth($paiement = null, $statut = null)
    {
        $startOfLastMonth = new \DateTime('first day of last month');
        $endOfLastMonth = new \DateTime('last day of last month');
        return $this->getTopOrdersByTotal($startOfLastMonth, $endOfLastMonth->modify('+1 day'), $paiement, $statut);
    }

    public function getTopOrdersByTotalThisYear($paiement = null, $statut = null)
    {
        $startOfYear = new \DateTime('first day of January this year');
        $endOfYear = new \DateTime('last day of December this year');
        return $this->getTopOrdersByTotal($startOfYear, $endOfYear->modify('+1 day'), $paiement, $statut);
    }

    public function getTopOrdersByTotalLastYear($paiement = null, $statut = null)
    {
        $startOfLastYear = new \DateTime('first day of January last year');
        $endOfLastYear = new \DateTime('last day of December last year');
        return $this->getTopOrdersByTotal($startOfLastYear, $endOfLastYear->modify('+1 day'), $paiement, $statut);
    }

    

}
