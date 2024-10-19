<?php

namespace App\Repository;

use App\Entity\Comptabilite;
use App\Service\ApplicationManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Comptabilite>
 */
class ComptabiliteRepository extends ServiceEntityRepository
{
    private $connection;
    private $application;

    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Comptabilite::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function findAllDates()
    {
        return $this->createQueryBuilder('c')
            ->select('c.id, c.dateComptabilite')
            ->leftJoin('c.application', 'a')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $this->application->getId())
            ->getQuery()
            ->getResult();
    }

    public function findAllByApplication()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.application', 'a')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $this->application->getId())
            ->getQuery()
            ->getResult();
    }
}
