<?php

namespace App\Repository;

use App\Entity\Benefice;
use Doctrine\DBAL\Connection;
use App\Service\ApplicationManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Benefice>
 */
class BeneficeRepository extends ServiceEntityRepository
{
    private $connection;
    private $application;

    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Benefice::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function findByApplication()
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.application', 'a')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $this->application->getId())
            ->getQuery()
            ->getResult();
    }

    public function findAllDate()
    {
        return $this->createQueryBuilder('b')
            ->select('b.id, b.dateBenefice')
            ->leftJoin('b.application', 'a')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $this->application->getId())
            ->getQuery()
            ->getResult();
    }
}
