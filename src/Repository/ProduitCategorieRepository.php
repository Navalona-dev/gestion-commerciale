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

    public function getProduits($affairesProduct = [])
    {
        $entityManager = $this->getEntityManager();

        $sql = "SELECT p.id, p.nom, p.stockRestant, p.reference, p.isChangePrix, p.prixAchat, p.prixHt, p.tva, p.prixTTC, p.qtt, p.stockMin, p.prixVenteGros, p.prixVenteDetail, p.uniteVenteGros, p.uniteVenteDetail, p.application_id, c.nom AS categorie 
        FROM `ProduitCategorie` p 
        LEFT JOIN `Categorie` c ON p.categorie_id = c.id 
        WHERE p.application_id = ".$this->application->getId()." 
        ";

        if (count($affairesProduct)> 0) {
            $sql .= " and p.id NOT IN (".implode(",",$affairesProduct ).")";
        }

        $sql .= " ORDER BY p.nom";
        
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

    public function findProductsByCompteAndApplication($compte, $application): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.comptes', 'c')
            ->innerJoin('p.application', 'a')
            ->andWhere('c.id = :compteId')
            ->andWhere('a.id = :applicationId')
            ->setParameter('compteId', $compte->getId())
            ->setParameter('applicationId', $application->getId())
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
    

    // Nombre de produit d'aujourd'hui
    public function countProductsToday()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $qb = $this->createQueryBuilder('p')
                    ->select('COUNT(p.id)')
                    ->where('p.dateCreation >= :today')
                    ->andWhere('p.dateCreation < :tomorrow')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();

               return $qb;
    }

     // Nombre de commandes d'hier
     public function countProductsYesterday()
     {
         $yesterday = new \DateTime();
         $yesterday->setTime(0, 0, 0);
         $yesterday->modify('-1 day');
         $today = clone $yesterday;
         $today->modify('+1 day');
 
         $qb = $this->createQueryBuilder('p')
                     ->select('COUNT(p.id)')
                     ->where('p.dateCreation >= :yesterday')
                     ->andWhere('p.dateCreation < :today')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('yesterday', $yesterday)
                     ->setParameter('today', $today)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

}
