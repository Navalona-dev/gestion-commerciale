<?php

namespace App\Repository;

use App\Entity\MethodePaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use App\Service\ApplicationManager;

/**
 * @extends ServiceEntityRepository<MethodePaiement>
 */
class MethodePaiementRepository extends ServiceEntityRepository
{
    private $connection;
    private $application;

    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, MethodePaiement::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function selectMethodeToday()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $qb = $this->createQueryBuilder('mp')
                    ->select('mp.id, mp.espece,mp.dateMethodePaiement, mp.mVola, mp.orangeMoney, mp.airtelMoney')
                    ->leftJoin('mp.application', 'a')
                    ->where('mp.dateMethodePaiement >= :today')
                    ->andWhere('mp.dateMethodePaiement < :tomorrow')
                    ->andWhere('a.id = :applicationId')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow)
                    ->setParameter('applicationId', $this->application->getId())
                    ->getQuery()
                    ->getResult();
               return $qb;
    }

    public function selectMethodeByDate($date)
    {
        $qb = $this->createQueryBuilder('mp')
                    ->select('mp.id, mp.espece, mp.mVola, mp.orangeMoney, mp.airtelMoney')
                    ->leftJoin('mp.application', 'a')
                    ->where('mp.dateMethodePaiement >= :date')
                    ->where('mp.dateMethodePaiement >= :startOfDay')
                    ->andWhere('mp.dateMethodePaiement <= :endOfDay')
                    ->andWhere('a.id = :applicationId')
                    ->setParameter('startOfDay', $date->format('Y-m-d') . ' 00:00:00') 
                    ->setParameter('endOfDay', $date->format('Y-m-d') . ' 23:59:59') 
                    ->setParameter('applicationId', $this->application->getId())
                    ->getQuery()
                    ->getResult();
               return $qb;
    }

    
}
