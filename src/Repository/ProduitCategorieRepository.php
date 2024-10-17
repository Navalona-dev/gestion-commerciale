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

        $sql = "SELECT p.id, p.nom, p.stockRestant, p.reference, p.isChangePrix, p.prixAchat, p.prixHt, p.tva, p.prixTTC, p.qtt, p.stockMin, p.prixVenteGros, p.prixVenteDetail, p.uniteVenteGros, p.uniteVenteDetail, p.application_id, p.presentationGros, p.presentationDetail, p.volumeGros, p.volumeDetail, c.nom AS categorie 
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

    public function getProduitsByStockRestant($affairesProduct = [])
    {

        $entityManager = $this->getEntityManager();

        $sql = "SELECT p.id, p.nom, p.stockRestant, p.reference, p.isChangePrix, p.prixAchat, p.prixHt, p.tva, p.prixTTC, p.qtt, p.stockMin, p.prixVenteGros, p.prixVenteDetail, p.uniteVenteGros, p.uniteVenteDetail, p.application_id, p.presentationGros, p.presentationDetail, p.volumeGros, p.volumeDetail, p.qttReserver, p.qttReserverGros, p.qttReserverDetail, c.nom AS categorie 
        FROM `ProduitCategorie` p 
        LEFT JOIN `Categorie` c ON p.categorie_id = c.id 
        WHERE p.application_id = ".$this->application->getId()." AND p.stockRestant > 0
        ";
        // AND p.stockRestant >= 1
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

     // Nombre de produit d'hier
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


     // Nombre de produit cette semaine
     public function countProductThisWeek()
     {
         $startOfWeek = new \DateTime();
         $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
         $startOfWeek->setTime(0, 0, 0);
 
         $endOfWeek = clone $startOfWeek;
         $endOfWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('p')
                     ->select('COUNT(p.id)')
                     ->where('p.dateCreation >= :start_of_week')
                     ->andWhere('p.dateCreation < :end_of_week')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_week', $startOfWeek)
                     ->setParameter('end_of_week', $endOfWeek)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de produit semaine dernière
      public function countProductsLastWeek()
      {
          $startOfLastWeek = new \DateTime();
          $startOfLastWeek->setISODate((int)$startOfLastWeek->format('o'), (int)$startOfLastWeek->format('W') - 1);
          $startOfLastWeek->setTime(0, 0, 0);
  
          $endOfLastWeek = clone $startOfLastWeek;
          $endOfLastWeek->modify('+7 days');
  
          $qb = $this->createQueryBuilder('p')
                      ->select('COUNT(p.id)')
                      ->where('p.dateCreation >= :start_of_last_week')
                      ->andWhere('p.dateCreation < :end_of_last_week')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('start_of_last_week', $startOfLastWeek)
                      ->setParameter('end_of_last_week', $endOfLastWeek)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

       // Nombre de produit ce mois-ci
     public function countProductsThisMonth()
     {
         $startOfMonth = new \DateTime('first day of this month');
         $startOfMonth->setTime(0, 0, 0);
 
         $endOfMonth = new \DateTime('first day of next month');
         $endOfMonth->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('p')
                     ->select('COUNT(p.id)')
                     ->where('p.dateCreation >= :start_of_month')
                     ->andWhere('p.dateCreation < :end_of_month')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_month', $startOfMonth)
                     ->setParameter('end_of_month', $endOfMonth)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de produit mois dernier
      public function countProductsLastMonth()
      {
          $startOfLastMonth = new \DateTime('first day of last month');
          $startOfLastMonth->setTime(0, 0, 0);
  
          $endOfLastMonth = new \DateTime('first day of this month');
          $endOfLastMonth->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('p')
                      ->select('COUNT(p.id)')
                      ->where('p.dateCreation >= :start_of_last_month')
                      ->andWhere('p.dateCreation < :end_of_last_month')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('start_of_last_month', $startOfLastMonth)
                      ->setParameter('end_of_last_month', $endOfLastMonth)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

      // Nombre de produit cette année
     public function countProductsThisYear()
     {
         $startOfYear = new \DateTime('first day of January this year');
         $startOfYear->setTime(0, 0, 0);
 
         $endOfYear = new \DateTime('first day of January next year');
         $endOfYear->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('p')
                     ->select('COUNT(p.id)')
                     ->where('p.dateCreation >= :start_of_year')
                     ->andWhere('p.dateCreation < :end_of_year')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_year', $startOfYear)
                     ->setParameter('end_of_year', $endOfYear)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de produit année dernière
      public function countProductsLastYear()
      {
          $startOfLastYear = new \DateTime('first day of January last year');
          $startOfLastYear->setTime(0, 0, 0);
  
          $endOfLastYear = new \DateTime('first day of January this year');
          $endOfLastYear->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('p')
                      ->select('COUNT(p.id)')
                      ->where('p.dateCreation >= :start_of_last_year')
                      ->andWhere('p.dateCreation < :end_of_last_year')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('start_of_last_year', $startOfLastYear)
                      ->setParameter('end_of_last_year', $endOfLastYear)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

      public function countStocks()
    {
        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(s.qtt)')
                    ->join('p.stocks', 's')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();

               return $qb;
    }

    //nombre stock restant
    public function countStockRestant()
    {
        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(p.stockRestant)')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();

               return $qb;
    }

      // Nombre de stock d'aujourd'hui
    public function countStocksToday()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(s.qtt)')
                    ->join('p.stocks', 's')
                    ->where('s.dateCreation >= :today')
                    ->andWhere('s.dateCreation < :tomorrow')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();

               return $qb;
    }

     // Nombre de stock d'hier
     public function countStocksYesterday()
     {
         $yesterday = new \DateTime();
         $yesterday->setTime(0, 0, 0);
         $yesterday->modify('-1 day');
         $today = clone $yesterday;
         $today->modify('+1 day');
 
         $qb = $this->createQueryBuilder('p')
                     ->select('SUM(s.qtt)')
                     ->join('p.stocks', 's')
                     ->where('s.dateCreation >= :yesterday')
                     ->andWhere('s.dateCreation < :today')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('yesterday', $yesterday)
                     ->setParameter('today', $today)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

     // Nombre de stock cette semaine
     public function countStockThisWeek()
     {
         $startOfWeek = new \DateTime();
         $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
         $startOfWeek->setTime(0, 0, 0);
 
         $endOfWeek = clone $startOfWeek;
         $endOfWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('p')
                     ->select('SUM(s.qtt)')
                     ->join('p.stocks', 's')
                     ->where('s.dateCreation >= :start_of_week')
                     ->andWhere('s.dateCreation < :end_of_week')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_week', $startOfWeek)
                     ->setParameter('end_of_week', $endOfWeek)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

     // Nombre de stock semaine dernière
     public function countStocksLastWeek()
     {
         $startOfLastWeek = new \DateTime();
         $startOfLastWeek->setISODate((int)$startOfLastWeek->format('o'), (int)$startOfLastWeek->format('W') - 1);
         $startOfLastWeek->setTime(0, 0, 0);
 
         $endOfLastWeek = clone $startOfLastWeek;
         $endOfLastWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('p')
                     ->select('SUM(s.qtt)')
                     ->join('p.stocks', 's')
                     ->where('s.dateCreation >= :start_of_last_week')
                     ->andWhere('s.dateCreation < :end_of_last_week')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_last_week', $startOfLastWeek)
                     ->setParameter('end_of_last_week', $endOfLastWeek)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

    // Nombre de stock ce mois-ci
    public function countStocksThisMonth()
    {
        $startOfMonth = new \DateTime('first day of this month');
        $startOfMonth->setTime(0, 0, 0);

        $endOfMonth = new \DateTime('first day of next month');
        $endOfMonth->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(s.qtt)')
                    ->join('p.stocks', 's')
                    ->where('s.dateCreation >= :start_of_month')
                    ->andWhere('s.dateCreation < :end_of_month')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('start_of_month', $startOfMonth)
                    ->setParameter('end_of_month', $endOfMonth)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
        return $qb;
    }

    // Nombre de stock mois dernier
    public function countStocksLastMonth()
    {
        $startOfLastMonth = new \DateTime('first day of last month');
        $startOfLastMonth->setTime(0, 0, 0);

        $endOfLastMonth = new \DateTime('first day of this month');
        $endOfLastMonth->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(s.qtt)')
                    ->join('p.stocks', 's')
                    ->where('s.dateCreation >= :start_of_last_month')
                    ->andWhere('s.dateCreation < :end_of_last_month')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('start_of_last_month', $startOfLastMonth)
                    ->setParameter('end_of_last_month', $endOfLastMonth)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
       return $qb;
    }

    // Nombre de stock cette année
    public function countStocksThisYear()
    {
        $startOfYear = new \DateTime('first day of January this year');
        $startOfYear->setTime(0, 0, 0);

        $endOfYear = new \DateTime('first day of January next year');
        $endOfYear->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(s.qtt)')
                    ->join('p.stocks', 's')
                    ->where('s.dateCreation >= :start_of_year')
                    ->andWhere('s.dateCreation < :end_of_year')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('start_of_year', $startOfYear)
                    ->setParameter('end_of_year', $endOfYear)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
       return $qb;
    }

    // Nombre de stock année dernière
    public function countStocksLastYear()
    {
        $startOfLastYear = new \DateTime('first day of January last year');
        $startOfLastYear->setTime(0, 0, 0);

        $endOfLastYear = new \DateTime('first day of January this year');
        $endOfLastYear->setTime(0, 0, 0);

        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(s.qtt)')
                    ->join('p.stocks', 's')
                    ->where('s.dateCreation >= :start_of_last_year')
                    ->andWhere('s.dateCreation < :end_of_last_year')
                    ->andWhere('p.application = :application_id')
                    ->setParameter('start_of_last_year', $startOfLastYear)
                    ->setParameter('end_of_last_year', $endOfLastYear)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
       return $qb;
    }
    

    // Nombre de stock restant d'aujourd'hui
    public function countStockRestantToday()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $qb = $this->createQueryBuilder('p')
                    ->select('SUM(p.stockRestant)')
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

     // Nombre de stock restant d'hier
     public function countStockRestantYesterday()
     {
         $yesterday = new \DateTime();
         $yesterday->setTime(0, 0, 0);
         $yesterday->modify('-1 day');
         $today = clone $yesterday;
         $today->modify('+1 day');
 
         $qb = $this->createQueryBuilder('p')
                     ->select('SUM(p.stockRestant)')
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


     // Nombre de stock restant cette semaine
     public function countStockRestantThisWeek()
     {
         $startOfWeek = new \DateTime();
         $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
         $startOfWeek->setTime(0, 0, 0);
 
         $endOfWeek = clone $startOfWeek;
         $endOfWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('p')
                     ->select('SUM(p.stockRestant)')
                     ->where('p.dateCreation >= :start_of_week')
                     ->andWhere('p.dateCreation < :end_of_week')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_week', $startOfWeek)
                     ->setParameter('end_of_week', $endOfWeek)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de stock restant semaine dernière
      public function countStockRestantLastWeek()
      {
          $startOfLastWeek = new \DateTime();
          $startOfLastWeek->setISODate((int)$startOfLastWeek->format('o'), (int)$startOfLastWeek->format('W') - 1);
          $startOfLastWeek->setTime(0, 0, 0);
  
          $endOfLastWeek = clone $startOfLastWeek;
          $endOfLastWeek->modify('+7 days');
  
          $qb = $this->createQueryBuilder('p')
                      ->select('SUM(p.stockRestant)')
                      ->where('p.dateCreation >= :start_of_last_week')
                      ->andWhere('p.dateCreation < :end_of_last_week')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('start_of_last_week', $startOfLastWeek)
                      ->setParameter('end_of_last_week', $endOfLastWeek)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

       // Nombre de stock restant ce mois-ci
     public function countStockRestantThisMonth()
     {
         $startOfMonth = new \DateTime('first day of this month');
         $startOfMonth->setTime(0, 0, 0);
 
         $endOfMonth = new \DateTime('first day of next month');
         $endOfMonth->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('p')
                     ->select('SUM(p.stockRestant)')
                     ->where('p.dateCreation >= :start_of_month')
                     ->andWhere('p.dateCreation < :end_of_month')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_month', $startOfMonth)
                     ->setParameter('end_of_month', $endOfMonth)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de stock restant mois dernier
      public function countStockRestantLastMonth()
      {
          $startOfLastMonth = new \DateTime('first day of last month');
          $startOfLastMonth->setTime(0, 0, 0);
  
          $endOfLastMonth = new \DateTime('first day of this month');
          $endOfLastMonth->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('p')
                      ->select('SUM(p.stockRestant)')
                      ->where('p.dateCreation >= :start_of_last_month')
                      ->andWhere('p.dateCreation < :end_of_last_month')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('start_of_last_month', $startOfLastMonth)
                      ->setParameter('end_of_last_month', $endOfLastMonth)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

      // Nombre de stock restant cette année
     public function countStockRestantThisYear()
     {
         $startOfYear = new \DateTime('first day of January this year');
         $startOfYear->setTime(0, 0, 0);
 
         $endOfYear = new \DateTime('first day of January next year');
         $endOfYear->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('p')
                     ->select('SUM(p.stockRestant)')
                     ->where('p.dateCreation >= :start_of_year')
                     ->andWhere('p.dateCreation < :end_of_year')
                     ->andWhere('p.application = :application_id')
                     ->setParameter('start_of_year', $startOfYear)
                     ->setParameter('end_of_year', $endOfYear)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de stock restant année dernière
      public function countStockRestantLastYear()
      {
          $startOfLastYear = new \DateTime('first day of January last year');
          $startOfLastYear->setTime(0, 0, 0);
  
          $endOfLastYear = new \DateTime('first day of January this year');
          $endOfLastYear->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('p')
                      ->select('SUM(p.stockRestant)')
                      ->where('p.dateCreation >= :start_of_last_year')
                      ->andWhere('p.dateCreation < :end_of_last_year')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('start_of_last_year', $startOfLastYear)
                      ->setParameter('end_of_last_year', $endOfLastYear)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

      public function countProduitDatePeremptionProche()
      {
          $qb = $this->createQueryBuilder('p')
                      ->select('COUNT(p.id)')
                      ->join('p.stocks', 's')
                      ->join('s.datePeremption', 'd')
                      ->where('d.date IS NOT NULL')
                      ->andWhere('s.qttRestant > 0')
                      ->andWhere('d.date BETWEEN CURRENT_DATE() AND :endDate')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('endDate', new \DateTime('+1 month'))
                      ->setParameter('application_id', $this->application->getId());
      
          return $qb->getQuery()->getSingleScalarResult();
      }

      public function produitDatePeremptionProche()
      {
          $qb = $this->createQueryBuilder('p')
                      ->select('p.reference, p.stockRestant, p.nom, s.qtt, s.qttRestant, d.date')
                      ->join('p.stocks', 's')
                      ->join('s.datePeremption', 'd')
                      ->where('d.date IS NOT NULL')
                      ->andWhere('s.qttRestant > 0')
                      ->andWhere('d.date BETWEEN CURRENT_DATE() AND :endDate')
                      ->andWhere('p.application = :application_id')
                      ->setParameter('endDate', new \DateTime('+1 month'))
                      ->setParameter('application_id', $this->application->getId())
                      ->orderBy('d.date', 'ASC');
      
          return $qb->getQuery()->getResult();
      }
      
}
