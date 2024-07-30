<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Affaire;
use App\Entity\ProduitCategorie;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }
    public function getCountAffairesToday($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesToday($paiement, $statut);
        return $countAffaire;
    }

    public function getCountAffairesYesterday($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesYesterday($paiement, $statut);
        return $countAffaire;
    }

    public function getCountAffairesThisWeek($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesThisWeek($paiement, $statut);
        return $countAffaire;
    }

    public function getCountAffairesLastWeek($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesLastWeek($paiement, $statut);
        return $countAffaire;
    }

    public function getCountAffairesThisMonth($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesThisMonth($paiement, $statut);
        return $countAffaire;
    }

    public function getCountAffairesLastMonth($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesLastMonth($paiement, $statut);
        return $countAffaire;
    }

    public function getCountAffairesThisYear($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesThisYear($paiement, $statut);
        return $countAffaire;
    }

    public function getCountAffairesLastYear($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countAffairesLastYear($paiement, $statut);
        return $countAffaire;
    }

    public function getCountProductsToday()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsToday();
        return $countProduit;
    }

    public function getCountProductsYesterday()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsYesterday();
        return $countProduit;
    }

    public function getCountProductsThisWeek()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductThisWeek();
        return $countProduit;
    }

    public function getCountProductsLastWeek()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsLastWeek();
        return $countProduit;
    }

    public function getCountProductsThisMonth()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsThisMonth();
        return $countProduit;
    }

    public function getCountProductsLastMonth()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsLastMonth();
        return $countProduit;
    }

    public function getCountProductsThisYear()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsThisYear();
        return $countProduit;
    }

    public function getCountProductsLastYear()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsLastYear();
        return $countProduit;
    }

    public function getCountStockToday()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocksToday();
        return $countStock;
    }

    public function getCountStockYesterday()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocksYesterday();
        return $countStock;
    }

    public function getCountStockThisWeek()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockThisWeek();
        return $countStock;
    }

    public function getCountStockLastWeek()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocksLastWeek();
        return $countStock;
    }

    public function getCountStockThisMonth()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocksThisMonth();
        return $countStock;
    }

    public function getCountStockLastMonth()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocksLastMonth();
        return $countStock;
    }

    public function getCountStockThisYear()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocksThisYear();
        return $countStock;
    }

    public function getCountStockLastYear()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocksLastYear();
        return $countStock;
    }

}