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

        $sql = "SELECT p.id, p.nom, p.stockRestant, p.reference, p.isChangePrix, p.prixAchat, p.prixHt, p.tva, p.prixTTC, p.qtt, p.stockMin, p.prixVenteGros, p.prixVenteDetail, p.uniteVenteGros, p.uniteVenteDetail, p.application_id, c.nom AS categorie 
        FROM `ProduitCategorie` p 
        LEFT JOIN `Categorie` c ON p.categorie_id = c.id 
        WHERE p.application_id = ".$this->application->getId()." 
        ORDER BY p.nom";

        //dd($sql);
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
    



}
