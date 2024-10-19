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

    public function getAllProducts($affaireId)
    {
        return $this->createQueryBuilder('p')
            ->join('p.affaires', 'a')
            ->where('a.id = :affaire_id')
            ->andWhere('p.qttVendu IS NULL OR p.qttVendu != p.qtt')
            ->setParameter('affaire_id', $affaireId)
            ->getQuery()
            ->getResult();
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


    public function findByAffairePaye($paiement = null, $statut = null, $reference = null)
    {
        return $this->createQueryBuilder('p')
            ->select('p.id as product_id, p.remise, p.reference, p.qtt, p.prixVenteGros, p.prixVenteDetail, p.typeVente, a.id as affaire_id, a.nom as affaire_nom, a.dateFacture, c.nom as compte_nom')
            ->join('p.affaires', 'a')
            ->join('a.compte', 'c')
            ->where('p.reference = :reference')
            ->andWhere('a.paiement = :paiement')
            ->andWhere('a.statut = :statut')
            ->andWhere('a.application = :application_id')
            ->setParameter('reference', $reference)
            ->setParameter('paiement', $paiement)
            ->setParameter('statut', $statut)
            ->setParameter('application_id', $this->application->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByAffairePaye($paiement = null, $statut = null, $reference = null)
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.qtt)')
            ->join('p.affaires', 'a')
            ->where('p.reference = :reference')
            ->andWhere('a.paiement = :paiement')
            ->andWhere('a.statut = :statut')
            ->andWhere('a.application = :application_id')
            ->setParameter('reference', $reference)
            ->setParameter('paiement', $paiement)
            ->setParameter('statut', $statut)
            ->setParameter('application_id', $this->application->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getQttByProduitAndTypeVente($paiement = null, $statut = null, $reference = null)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.qtt, p.typeVente')
            ->join('p.affaires', 'a')
            ->where('p.reference = :reference')
            //->andWhere('a.paiement = :paiement')
            ->andWhere('a.paiement IN (:paiement)')
            ->andWhere('a.statut = :statut')
            ->andWhere('a.application = :application_id')
            ->setParameter('reference', $reference)
            ->setParameter('paiement', $paiement)
            ->setParameter('statut', $statut)
            ->setParameter('application_id', $this->application->getId());

        return $qb->getQuery()->getResult();
    }



}
