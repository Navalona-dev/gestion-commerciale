<?php

namespace App\Repository;

use App\Entity\Product;
use App\Service\ApplicationManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    private $connection;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Product::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function findProduitAffaire($affaire)
    {
        $query = $this->createQueryBuilder('p')
                ->join('p.affaires', 'a')
                ->where('p.application = :application')
            ->setParameter('application', $this->application)
            ->andWhere('a.id = :affaire')
            ->setParameter('affaire', $affaire)
        ;

        $query = $query->addOrderBy('p.dateCreation', 'DESC');


        return $query->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
