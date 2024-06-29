<?php

namespace App\Repository;

use App\Entity\ProduitCategorie;
use App\Service\ApplicationManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
/**
 * @extends ServiceEntityRepository<ProduitCategorie>
 */
class ProduitCategorieRepository extends ServiceEntityRepository
{
    private $connection;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, ProduitCategorie::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function getProduits()
    {
        $entityManager = $this->getEntityManager();

        $sql = "SELECT id, nom, stockRestant FROM `ProduitCategorie` WHERE `application_id` = ".$this->application->getId()."  order by nom";

        $query = $this->connection->prepare($sql);
        
        $query = $this->connection->executeQuery($sql);

        $produits = $query->fetchAll();
        if (sizeof($produits) > 0) {
            return $produits;
        }
        return false;
    }

    //    /**
    //     * @return ProduitCategorie[] Returns an array of ProduitCategorie objects
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

    //    public function findOneBySomeField($value): ?ProduitCategorie
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
