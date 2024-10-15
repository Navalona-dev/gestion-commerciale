<?php

namespace App\Repository;

use App\Entity\Fourchette;
use App\Service\ApplicationManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Fourchette>
 */
class FourchetteRepository extends ServiceEntityRepository
{
    private $connection;
    private $application;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Fourchette::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function findByApplication()
    {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.application', 'a')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $this->application->getId())
            ->getQuery()
            ->getResult();
    }

   
}
