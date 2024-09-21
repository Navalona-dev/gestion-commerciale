<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function findAllApplication(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('a.entreprise', 'ASC') 
            ->getQuery()
            ->getResult();
    }
    public function findByApplication($applicationId): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.entreprise, a.id')
            ->andWhere('a.isActive = :isActive')
            ->andWhere('a.id != :currentAppId')
            ->setParameter('isActive', true)
            ->setParameter('currentAppId', $applicationId)
            ->orderBy('a.entreprise', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
