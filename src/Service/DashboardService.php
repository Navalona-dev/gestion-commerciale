<?php
namespace App\Service;

use App\Entity\Affaire;
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
}