<?php

namespace App\Repository;

use App\Entity\Categoryofpermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Categoryofpermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categoryofpermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categoryofpermission[]    findAll()
 * @method Categoryofpermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryofpermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoryofpermission::class);
    }

    // /**
    //  * @return Categoryofpermission[] Returns an array of Categoryofpermission objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Categoryofpermission
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
