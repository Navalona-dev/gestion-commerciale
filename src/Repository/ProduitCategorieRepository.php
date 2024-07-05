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

        $sql = "SELECT id, nom, stockRestant, reference FROM `ProduitCategorie` WHERE `application_id` = ".$this->application->getId()."  order by nom";

        $query = $this->connection->prepare($sql);
        
        $query = $this->connection->executeQuery($sql);

        $produits = $query->fetchAll();
        if (sizeof($produits) > 0) {
            return $produits;
        }
        return false;
    }
    
    public function getAllFournisseur()
    {
        
        $sql = "SELECT c.nom, c.id FROM compte as c WHERE c.genre = 2 and c.application_id = ".$this->application->getId()." ORDER BY c.dateCreation DESC";
        
        $query = $this->connection->executeQuery($sql);
        return $query->fetchAll();

    }

    public function findReferenceProduitByApplication($application): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.reference') 
            ->innerJoin('p.application', 'a') 
            ->andWhere('a.id = :application') 
            ->setParameter('application', $application)
            ->orderBy('p.reference', 'ASC') 
            ->getQuery()
            ->getResult();
    }
}
