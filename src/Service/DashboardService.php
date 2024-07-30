<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Compte;
use App\Entity\Affaire;
use App\Entity\Transfert;
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

    public function getCountStockRestantToday()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantToday();
        return $countStock;
    }

    public function getCountStockRestantYesterday()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantYesterday();
        return $countStock;
    }

    public function getCountStockRestantThisWeek()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantThisWeek();
        return $countStock;
    }

    public function getCountStockRestantLastWeek()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantLastWeek();
        return $countStock;
    }

    public function getCountStockRestantThisMonth()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantThisMonth();
        return $countStock;
    }

    public function getCountStockRestantLastMonth()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantLastMonth();
        return $countStock;
    }

    public function getCountStockRestantThisYear()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantThisYear();
        return $countStock;
    }

    public function getCountStockRestantLastYear()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestantLastYear();
        return $countStock;
    }

    public function getCountStockVenduToday($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStocksVenduToday($paiement, $statut);
        return $countAffaire;
    }

    public function getCountStockVenduYesterday($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStocksVenduYesterday($paiement, $statut);
        return $countAffaire;
    }

    public function getCountStockVenduThisWeek($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStockVenduThisWeek($paiement, $statut);
        return $countAffaire;
    }

    public function getCountStockVenduLastWeek($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStocksVenduLastWeek($paiement, $statut);
        return $countAffaire;
    }

    public function getCountStockVenduThisMonth($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStocksVenduThisMonth($paiement, $statut);
        return $countAffaire;
    }

    public function getCountStockVenduLastMonth($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStocksVenduLastMonth($paiement, $statut);
        return $countAffaire;
    }

    public function getCountStockVenduThisYear($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStocksVenduThisYear($paiement, $statut);
        return $countAffaire;
    }

    public function getCountStockVenduLastYear($paiement = null, $statut = null)
    {
        $countAffaire = $this->entityManager->getRepository(Affaire::class)->countStocksVenduLastYear($paiement, $statut);
        return $countAffaire;
    }

    public function getCountCompteToday($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesToday($genre);
        return $countCompte;
    }

    public function getCountCompteYesterday($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesYesterday($genre);
        return $countCompte;
    }

    public function getCountCompteThisWeek($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesThisWeek($genre);
        return $countCompte;
    }

    public function getCountCompteLastWeek($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesLastWeek($genre);
        return $countCompte;
    }

    public function getCountCompteThisMonth($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesThisMonth($genre);
        return $countCompte;
    }

    public function getCountCompteLastMonth($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesLastMonth($genre);
        return $countCompte;
    }

    public function getCountCompteThisYear($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesThisYear($genre);
        return $countCompte;
    }

    public function getCountCompteLastYear($genre = null)
    {
        $countCompte = $this->entityManager->getRepository(Compte::class)->countComptesLastYear($genre);
        return $countCompte;
    }

    public function getCountTransfertToday()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countTransfertsToday();
        return $countTransfert;
    }

    public function getCountTransfertYesterday()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countTransfertsYesterday();
        return $countTransfert;
    }

    public function getCountTransfertThisWeek()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countProductThisWeek();
        return $countTransfert;
    }

    public function getCountTransfertLastWeek()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countTransfertsLastWeek();
        return $countTransfert;
    }

    public function getCountTransfertThisMonth()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countTransfertsThisMonth();
        return $countTransfert;
    }

    public function getCountTransfertLastMonth()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countTransfertsLastMonth();
        return $countTransfert;
    }

    public function getCountTransfertThisYear()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countTransfertsThisYear();
        return $countTransfert;
    }

    public function getCountTransfertLastYear()
    {
        $countTransfert = $this->entityManager->getRepository(Transfert::class)->countTransfertsLastYear();
        return $countTransfert;
    }

}