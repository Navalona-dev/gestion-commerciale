<?php

namespace App\Repository;

use App\Entity\ProduitType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitType>
 */
class ProduitTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitType::class);
    }

    //    /**
    //     * @return ProduitType[] Returns an array of ProduitType objects
    //     */
    public function findNameTypeByApplication($application): array
    {
        return $this->createQueryBuilder('pt')
            ->select('pt.nom') 
            ->innerJoin('pt.application', 'a') 
            ->andWhere('a.id = :application') 
            ->setParameter('application', $application)
            ->orderBy('pt.nom', 'ASC') 
            ->getQuery()
            ->getResult();
    }
}
