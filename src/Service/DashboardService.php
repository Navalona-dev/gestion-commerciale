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

    public function getCountProduitDatePeremptionProche()
    {
        $countProduit = $this->entityManager->getRepository(ProduitCategorie::class)->countProduitDatePeremptionProche();
        return $countProduit;
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

    public function getCountStocks()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStocks();
        return $countStock;
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

    public function getCountStockRestant()
    {
        $countStock = $this->entityManager->getRepository(ProduitCategorie::class)->countStockRestant();
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

    //chart

    public function getOrdersCountByDayThisWeek($paiement = null, $statut = null): array
    {
        $startOfWeek = new \DateTime();
        $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
        $startOfWeek->setTime(0, 0, 0);

        $counts = [];
        for ($i = 1; $i <= 7; $i++) { // Lundi à Dimanche (1 = Lundi, 2 = Mardi, ..., 7 = Dimanche)
            $dayOfWeek = (clone $startOfWeek)->modify("+$i days -1 day");
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countOrdersThisWeekByDay($dayOfWeek, $paiement, $statut );
        }

        return $counts;
    }

    public function getOrdersCountByDayLastWeek($paiement = null, $statut = null): array
    {
        $counts = [];
        for ($i = 1; $i <= 7; $i++) { // Lundi à Dimanche (1 = Lundi, 2 = Mardi, ..., 7 = Dimanche)
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countOrdersLastWeekByDay($i, $paiement, $statut );
            
        }

        return $counts;
    }

    // Commandes par mois cette année
    public function getOrdersCountThisYearByMonth($paiement = null, $statut = null): array
    {
        $currentYear = date('Y');
        $counts = [];
        for ($month = 1; $month <= 12; $month++) {
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countOrdersThisYearByMonth($currentYear, $month, $paiement, $statut );
        }
        return $counts;
    }

    // Commandes par mois l'année dernière
    public function getOrdersCountLastYearByMonth($paiement = null, $statut = null): array
    {
        $lastYear = date('Y') - 1;
        $counts = [];
        for ($month = 1; $month <= 12; $month++) {
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countOrdersLastYearByMonth($lastYear, $month, $paiement, $statut );
        }
        return $counts;
    }

    public function getClientsCountByDayThisWeek($genre = null): array
    {
        $counts = [];
        $currentDate = new \DateTime();
        for ($i = 1; $i <= 7; $i++) { // Lundi à Dimanche (1 = Lundi, 2 = Mardi, ..., 7 = Dimanche)
            $specificDay = clone $currentDate;
            $specificDay->setISODate((int)$currentDate->format('o'), (int)$currentDate->format('W'));
            $specificDay->modify('+' . ($i - 1) . ' days');
            $counts[] = $this->entityManager->getRepository(Compte::class)->countClientsThisWeekByDay($specificDay, $genre);
        }
        return $counts;
    }

    public function getClientsCountByDayLastWeek($genre): array
    {
        $counts = [];
        for ($i = 1; $i <= 7; $i++) { // Lundi à Dimanche (1 = Lundi, 2 = Mardi, ..., 7 = Dimanche)
            $counts[] = $this->entityManager->getRepository(Compte::class)->countClientsLastWeekByDay($i, $genre);
        }
        return $counts;
    }

    // Nombre de clients inscrits par mois cette année
    public function getClientsRegisteredThisYearByMonth($genre): array
    {
        $currentYear = date('Y');
        $counts = [];
        for ($month = 1; $month <= 12; $month++) {
            $counts[] = $this->entityManager->getRepository(Compte::class)->countClientsRegisteredThisYearByMonth($currentYear, $month, $genre);
        }
        return $counts;
    }

    // Nombre de clients inscrits par mois l'année dernière
    public function getClientsRegisteredLastYearByMonth($genre): array
    {
        $lastYear = date('Y') - 1;
        $counts = [];
        for ($month = 1; $month <= 12; $month++) {
            $counts[] = $this->entityManager->getRepository(Compte::class)->countClientsRegisteredLastYearByMonth($lastYear, $month, $genre);
        }
        return $counts;
    }

    public function getProductsSoldThisWeekByDay($paiement = null, $statut = null): array
    {
        $counts = [];
        for ($i = 1; $i <= 7; $i++) { // Lundi à Dimanche (1 = Lundi, 2 = Mardi, ..., 7 = Dimanche)
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countProductsSoldThisWeekByDay($i, $paiement, $statut);
        }

        return $counts;
    }


    public function getProductsSoldLastWeekByDay($paiement = null, $statut = null): array
    {
        $counts = [];
        for ($i = 1; $i <= 7; $i++) { // Lundi à Dimanche (1 = Lundi, 2 = Mardi, ..., 7 = Dimanche)
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countProductsSoldLastWeekByDay($i, $paiement, $statut);
        }

        return $counts;
    }

    // Produits vendus par mois cette année
    public function getProductsSoldThisYearByMonth($paiement = null, $statut = null): array
    {
        $currentYear = date('Y');
        $counts = [];
        for ($month = 1; $month <= 12; $month++) {
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countProductsSoldThisYearByMonth($currentYear, $month, $paiement, $statut);
        }
        return $counts;
    }

    // Produits vendus par mois l'année dernière
    public function getProductsSoldLastYearByMonth($paiement = null, $statut = null): array
    {
        $lastYear = date('Y') - 1;
        $counts = [];
        for ($month = 1; $month <= 12; $month++) {
            $counts[] = $this->entityManager->getRepository(Affaire::class)->countProductsSoldLastYearByMonth($lastYear, $month, $paiement, $statut);
        }
        return $counts;
    }

    public function getTopOrdersByTotalToday($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalToday($paiement, $statut);
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }

    public function getTopOrdersByTotalYesterday($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalYesterday($paiement, $statut);
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }

    public function getTopOrdersByTotalThisWeek($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalThisWeek($paiement, $statut);
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }

    public function getTopOrdersByTotalLastWeek($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalLastWeek($paiement, $statut);
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }

    public function getTopOrdersByTotalThisMonth($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalThisMonth($paiement, $statut);
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }

    public function getTopOrdersByTotalLastMonth($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalLastMonth($paiement, $statut);
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }

    public function getTopOrdersByTotalThisYear($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalThisYear($paiement, $statut );
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }

    public function getTopOrdersByTotalLastYear($paiement = null, $statut = null)
    {
        $affaires = $this->entityManager->getRepository(Affaire::class)->getTopOrdersByTotalLastYear($paiement , $statut );
        if ($affaires != false && count($affaires) > 0) {
            return $affaires;
        }
        return false;
    }


}