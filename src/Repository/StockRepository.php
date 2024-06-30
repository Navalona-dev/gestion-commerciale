<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    //    /**
    //     * @return Stock[] Returns an array of Stock objects
    //     */
    public function findByProductCategory($produitCategorie): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc')
            ->andWhere('pc.id = :produit_categorie_id')
            ->setParameter('produit_categorie_id', $produitCategorie->getId())
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

}
