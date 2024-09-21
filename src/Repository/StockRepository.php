<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Stock>
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    //    /**
    //     * @return Stock[] Returns an array of Stock objects
    //     */
    /*public function findByProductCategory($produitCategorie): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc')
            ->andWhere('pc.id = :produit_categorie_id')
            ->setParameter('produit_categorie_id', $produitCategorie->getId())
            ->orderBy('s.datePeremption', 'ASC')
            ->getQuery()
            ->getResult();
    }*/

    public function findByProductCategory($produitCategorie)
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc') 
            ->leftJoin('s.datePeremption', 'd')
            ->where('pc.id = :produit_categorie_id') 
            ->orderBy('d.date', 'ASC') 
            ->setParameter('produit_categorie_id', $produitCategorie->getId()) 
            ->getQuery() 
            ->getResult(); 
    }


    public function findOneByProduitCategorie($produitCategorie)
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc') // Joindre la table produitCategorie
            ->join('s.datePeremption', 'd')
            ->where('pc.id = :produit_categorie_id') // Filtrer par produitCategorie
            ->andWhere('s.qttRestant > 0') // Filtrer par quantité restante
            ->orderBy('d.date', 'ASC') // Trier par date de péremption la plus proche
            ->setParameter('produit_categorie_id', $produitCategorie->getId()) // Assigner le paramètre
            ->setMaxResults(1) // Limiter le résultat à un seul enregistrement
            ->getQuery() // Obtenir la requête
            ->getOneOrNullResult(); // Exécuter la requête et obtenir un résultat ou null
    }
    
    public function findByProduitCategorieDatePerremptionIsNotNull($produitCategorie)
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc') 
            ->join('s.datePeremption', 'd')
            ->where('pc.id = :produit_categorie_id') 
            ->andWhere('s.qttRestant > 0') 
            ->andWhere('d.date IS NOT NULL')
            ->orderBy('d.date', 'ASC') 
            ->setParameter('produit_categorie_id', $produitCategorie->getId()) 
            ->getQuery() 
            ->getResult(); 
    }

    public function findByProduitCategorieDatePerremption($produitCategorie)
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc') 
            ->join('s.datePeremption', 'd')
            ->where('pc.id = :produit_categorie_id') 
            ->andWhere('d.date IS NOT NULL')
            ->orderBy('d.date', 'ASC') 
            ->setParameter('produit_categorie_id', $produitCategorie->getId()) 
            ->getQuery() 
            ->getResult(); 
    }

    public function findByProductCategoryDatePeremption($produitCategorie)
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc') 
            ->leftJoin('s.datePeremption', 'd') // Utiliser LEFT JOIN pour inclure les stocks sans date de péremption
            ->where('pc.id = :produit_categorie_id') 
            ->andWhere('s.qttRestant > 0') 
            ->orderBy('CASE WHEN d.date IS NULL THEN 1 ELSE 0 END', 'ASC') // Priorise les dates non nulles
            ->addOrderBy('d.date', 'ASC') // Puis trier par date de péremption
            ->setParameter('produit_categorie_id', $produitCategorie->getId()) 
            ->getQuery() 
            ->getResult(); 
    }

    public function findByProduitCategorieAnulle($produitCategorie)
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc') 
            ->leftJoin('s.datePeremption', 'd') // Utiliser LEFT JOIN pour inclure les stocks sans date de péremption
            ->where('pc.id = :produit_categorie_id') 
            ->orderBy('CASE WHEN d.date IS NULL THEN 1 ELSE 0 END', 'ASC') // Priorise les dates non nulles
            ->addOrderBy('d.date', 'ASC') // Puis trier par date de péremption
            ->setParameter('produit_categorie_id', $produitCategorie->getId()) 
            ->getQuery() 
            ->getResult(); 
    }

    public function findOneByDatePeremption($produitCategorie, $date)
    {
        return $this->createQueryBuilder('s')
            ->join('s.produitCategorie', 'pc')
            ->join('s.datePeremption', 'dp') 
            ->where('pc.id = :produit_categorie_id') 
            ->andWhere('dp.date = :date') 
            ->setParameter('produit_categorie_id', $produitCategorie->getId()) 
            ->setParameter('date', $date) 
            ->getQuery() 
            ->getOneOrNullResult(); 
    }

}
