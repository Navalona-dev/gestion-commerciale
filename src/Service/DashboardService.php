<?php
namespace App\Service;

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
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsToday();
        return $countAffaire;
    }

    public function getCountProductsYesterday()
    {
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsYesterday();
        return $countAffaire;
    }

    public function getCountProductsThisWeek()
    {
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductThisWeek();
        return $countAffaire;
    }

    public function getCountProductsLastWeek()
    {
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsLastWeek();
        return $countAffaire;
    }

    public function getCountProductsThisMonth()
    {
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsThisMonth();
        return $countAffaire;
    }

    public function getCountProductsLastMonth()
    {
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsLastMonth();
        return $countAffaire;
    }

    public function getCountProductsThisYear()
    {
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsThisYear();
        return $countAffaire;
    }

    public function getCountProductsLastYear()
    {
        $countAffaire = $this->entityManager->getRepository(ProduitCategorie::class)->countProductsLastYear();
        return $countAffaire;
    }

}