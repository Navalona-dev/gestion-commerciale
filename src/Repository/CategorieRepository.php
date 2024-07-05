<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Service\ApplicationManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    private $application;
    private $connection;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Categorie::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
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

    public function getAllCategories()
    {
        $entityManager = $this->getEntityManager();

        $sql = "SELECT id, nom, etat, dateCreation FROM `Categorie` WHERE `application_id` = ".$this->application->getId()."  order by nom";

        $query = $this->connection->prepare($sql);
        
        $query = $this->connection->executeQuery($sql);

        $produits = $query->fetchAll();
        if (sizeof($produits) > 0) {
            return $produits;
        }
        return false;
    }

    
}
