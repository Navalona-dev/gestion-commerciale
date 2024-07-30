<?php

namespace App\Repository;

use App\Entity\Transfert;
use App\Service\ApplicationManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Transfert>
 */
class TransfertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, ApplicationManager    $applicationManager)
    {
        parent::__construct($registry, Transfert::class);
        $this->application = $applicationManager->getApplicationActive();

    }

    //    /**
    //     * @return Transfert[] Returns an array of Transfert objects
    //     */
       public function findByProduit($produit): array
       {
           return $this->createQueryBuilder('t')
               ->andWhere('t.produitCategorie = :produit')
               ->setParameter('produit', $produit)
               ->orderBy('t.dateCreation', 'ASC')
               ->getQuery()
               ->getResult()
           ;
       }

           // Nombre de transfert d'aujourd'hui
    public function countTransfertsToday()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $qb = $this->createQueryBuilder('t')
                    ->select('SUM(t.quantity)')
                    ->where('t.dateCreation >= :today')
                    ->andWhere('t.dateCreation < :tomorrow')
                    ->andWhere('t.oldApplication = :oldApplication_id')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow)
                    ->setParameter('oldApplication_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();

               return $qb;
    }

     // Nombre de transfert d'hier
     public function countTransfertsYesterday()
     {
         $yesterday = new \DateTime();
         $yesterday->setTime(0, 0, 0);
         $yesterday->modify('-1 day');
         $today = clone $yesterday;
         $today->modify('+1 day');
 
         $qb = $this->createQueryBuilder('t')
                     ->select('SUM(t.quantity)')
                     ->where('t.dateCreation >= :yesterday')
                     ->andWhere('t.dateCreation < :today')
                     ->andWhere('t.oldApplication = :oldApplication_id')
                     ->setParameter('yesterday', $yesterday)
                     ->setParameter('today', $today)
                     ->setParameter('oldApplication_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }


     // Nombre de transfert cette semaine
     public function countProductThisWeek()
     {
         $startOfWeek = new \DateTime();
         $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
         $startOfWeek->setTime(0, 0, 0);
 
         $endOfWeek = clone $startOfWeek;
         $endOfWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('t')
                     ->select('SUM(t.quantity)')
                     ->where('t.dateCreation >= :start_of_week')
                     ->andWhere('t.dateCreation < :end_of_week')
                     ->andWhere('t.oldApplication = :oldApplication_id')
                     ->setParameter('start_of_week', $startOfWeek)
                     ->setParameter('end_of_week', $endOfWeek)
                     ->setParameter('oldApplication_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de transfert semaine dernière
      public function countTransfertsLastWeek()
      {
          $startOfLastWeek = new \DateTime();
          $startOfLastWeek->setISODate((int)$startOfLastWeek->format('o'), (int)$startOfLastWeek->format('W') - 1);
          $startOfLastWeek->setTime(0, 0, 0);
  
          $endOfLastWeek = clone $startOfLastWeek;
          $endOfLastWeek->modify('+7 days');
  
          $qb = $this->createQueryBuilder('t')
                      ->select('SUM(t.quantity)')
                      ->where('t.dateCreation >= :start_of_last_week')
                      ->andWhere('t.dateCreation < :end_of_last_week')
                      ->andWhere('t.oldApplication = :oldApplication_id')
                      ->setParameter('start_of_last_week', $startOfLastWeek)
                      ->setParameter('end_of_last_week', $endOfLastWeek)
                      ->setParameter('oldApplication_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

       // Nombre de transfert ce mois-ci
     public function countTransfertsThisMonth()
     {
         $startOfMonth = new \DateTime('first day of this month');
         $startOfMonth->setTime(0, 0, 0);
 
         $endOfMonth = new \DateTime('first day of next month');
         $endOfMonth->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('t')
                     ->select('SUM(t.quantity)')
                     ->where('t.dateCreation >= :start_of_month')
                     ->andWhere('t.dateCreation < :end_of_month')
                     ->andWhere('t.oldApplication = :oldApplication_id')
                     ->setParameter('start_of_month', $startOfMonth)
                     ->setParameter('end_of_month', $endOfMonth)
                     ->setParameter('oldApplication_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de transfert mois dernier
      public function countTransfertsLastMonth()
      {
          $startOfLastMonth = new \DateTime('first day of last month');
          $startOfLastMonth->setTime(0, 0, 0);
  
          $endOfLastMonth = new \DateTime('first day of this month');
          $endOfLastMonth->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('t')
                      ->select('SUM(t.quantity)')
                      ->where('t.dateCreation >= :start_of_last_month')
                      ->andWhere('t.dateCreation < :end_of_last_month')
                      ->andWhere('t.oldApplication = :oldApplication_id')
                      ->setParameter('start_of_last_month', $startOfLastMonth)
                      ->setParameter('end_of_last_month', $endOfLastMonth)
                      ->setParameter('oldApplication_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

      // Nombre de transfert cette année
     public function countTransfertsThisYear()
     {
         $startOfYear = new \DateTime('first day of January this year');
         $startOfYear->setTime(0, 0, 0);
 
         $endOfYear = new \DateTime('first day of January next year');
         $endOfYear->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('t')
                     ->select('SUM(t.quantity)')
                     ->where('t.dateCreation >= :start_of_year')
                     ->andWhere('t.dateCreation < :end_of_year')
                     ->andWhere('t.oldApplication = :oldApplication_id')
                     ->setParameter('start_of_year', $startOfYear)
                     ->setParameter('end_of_year', $endOfYear)
                     ->setParameter('oldApplication_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de transfert année dernière
      public function countTransfertsLastYear()
      {
          $startOfLastYear = new \DateTime('first day of January last year');
          $startOfLastYear->setTime(0, 0, 0);
  
          $endOfLastYear = new \DateTime('first day of January this year');
          $endOfLastYear->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('t')
                      ->select('SUM(t.quantity)')
                      ->where('t.dateCreation >= :start_of_last_year')
                      ->andWhere('t.dateCreation < :end_of_last_year')
                      ->andWhere('t.oldApplication = :oldApplication_id')
                      ->setParameter('start_of_last_year', $startOfLastYear)
                      ->setParameter('end_of_last_year', $endOfLastYear)
                      ->setParameter('oldApplication_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

}
