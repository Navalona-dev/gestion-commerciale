<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    //    /**
    //     * @return Categorie[] Returns an array of Categorie objects
    //     */
    public function findNameCategoriesByApplication($application): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom') 
            ->innerJoin('c.application', 'a') 
            ->andWhere('a.id = :application') 
            ->setParameter('application', $application)
            ->orderBy('c.nom', 'ASC') 
            ->getQuery()
            ->getResult();
    }

    
}
