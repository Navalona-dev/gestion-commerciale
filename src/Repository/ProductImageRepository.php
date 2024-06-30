<?php

namespace App\Repository;

use App\Entity\ProductImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage>
 */
class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }

    //    /**
    //     * @return ProductImage[] Returns an array of ProductImage objects
    //     */
    public function findByProductCategory($produitCategorie): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.produitCategorie', 'pc')
            ->andWhere('pc.id = :produit_categorie_id')
            ->setParameter('produit_categorie_id', $produitCategorie->getId())
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
