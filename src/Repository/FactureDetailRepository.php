<?php

namespace App\Repository;

use App\Entity\FactureDetail;
use App\Service\ApplicationManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<FactureDetail>
 */
class FactureDetailRepository extends ServiceEntityRepository
{
    private $connection;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, FactureDetail::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }


    public function getProduitsVenduByReference($reference = null)
    {
        $entityManager = $this->getEntityManager();

        $sql = "SELECT SUM(fd.qtt) as qttVendu
        FROM `FactureDetail` fd 
        LEFT JOIN `Facture` f ON f.id = fd.facture_id 
        WHERE f.application_id = ".$this->application->getId()." and fd.reference = '".$reference."'";

        //dd($sql);
        $query = $this->connection->prepare($sql);
        
        $query = $this->connection->executeQuery($sql);

        $produits = $query->fetchAll();
        if (sizeof($produits) > 0) {
            return $produits[0];
        }
        return false;
    }
    //    /**
    //     * @return FactureDetail[] Returns an array of FactureDetail objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FactureDetail
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
