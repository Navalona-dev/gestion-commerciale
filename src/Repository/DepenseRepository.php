<?php

namespace App\Repository;

use App\Entity\Depense;
use App\Service\ApplicationManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Depense>
 */
class DepenseRepository extends ServiceEntityRepository
{
    private $application;
    private $connection;

    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Depense::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function selectDepenseToday()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $qb = $this->createQueryBuilder('d')
                    ->select('d.id, d.dateCreation, d.total, d.prix, d.designation, d.nombre')
                    ->where('d.dateCreation >= :today')
                    ->andWhere('d.dateCreation < :tomorrow')
                    ->andWhere('d.application = :application_id')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getResult();

               return $qb;
    }

    
}