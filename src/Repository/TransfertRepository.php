<?php

namespace App\Repository;

use App\Entity\Transfert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transfert>
 */
class TransfertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transfert::class);
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

}
