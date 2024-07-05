<?php

namespace App\Repository;

use App\Entity\ProduitType;
use App\Service\ApplicationManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;


/**
 * @extends ServiceEntityRepository<ProduitType>
 */
class ProduitTypeRepository extends ServiceEntityRepository
{
    private $application;
    private $connection;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, ProduitType::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
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

    public function getAllTypes()
    {
        $entityManager = $this->getEntityManager();

        $sql = "SELECT id, nom, isActive, dateCreation, description FROM `ProduitType` WHERE `application_id` = ".$this->application->getId()."  order by nom";

        $query = $this->connection->prepare($sql);
        
        $query = $this->connection->executeQuery($sql);

        $produits = $query->fetchAll();
        if (sizeof($produits) > 0) {
            return $produits;
        }
        return false;
    }
}
