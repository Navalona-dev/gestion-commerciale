<?php

namespace App\Repository;

use App\Entity\Facture;
use App\Service\ApplicationManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    private $connection;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Facture::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function getLastValideFacture($type = "Facture")
    {
        $query = $this->createQueryBuilder('f')
            ->join('f.compte', 'c')
            
            ->andWhere('f.type = :type AND (f.isValid = 1 OR f.numero is not null)')
            ->setParameter('type', $type)
            ;
   
        $query = $query
        ->andWhere("f.application = :application")
        ->setParameter('application', $this->application)
        ;
   
        $results = $query->getQuery()->getResult();

        $tabNumero = [];

        if ($results) {
            foreach ($results as $result) {
                $tabNumero [] = intval($result->getNumero());
            }

            arsort($tabNumero);

            //Pour ordonner la clé
            $tabNumero = array_merge($tabNumero, []);
        }

        return $tabNumero;
    }

    public function getAllFactures()
    {
        $sql = "SELECT f.type, f.numero, f.prixHt, f.prixTtc, f.solde, f.statut, f.reglement, f.numeroCommande, f.etat,
                f.file, f.dateCreation, f.isValid, f.remise, c.nom as compte, a.nom as affaire
                FROM `Facture` f 
                LEFT JOIN `compte` c ON f.compte_id = c.id 
                LEFT JOIN `affaire` a ON f.affaire_id = a.id 
                WHERE f.application_id = ".$this->application->getId()." 
                ORDER BY f.dateCreation DESC";

            $query = $this->connection->prepare($sql);
        
            $query = $this->connection->executeQuery($sql);
    
            return $query->fetchAll(); 
    }

    public function getAllFacturesByAffaire($affaireId = null)
    {
        $sql = "SELECT f.type, f.numero, f.prixHt, f.prixTtc, f.solde, f.statut, f.reglement, f.numeroCommande, f.etat,
                f.file, f.dateCreation, f.isValid, f.remise, c.nom as compte, a.nom as affaire
                FROM `Facture` f 
                LEFT JOIN `compte` c ON f.compte_id = c.id 
                LEFT JOIN `affaire` a ON f.affaire_id = a.id 
                WHERE f.application_id = ".$this->application->getId()."";

                if ($affaireId != null) {
                    $sql .= " and a.id = ".$affaireId."";
                }

            $sql .= " ORDER BY f.dateCreation DESC";


            $query = $this->connection->prepare($sql);
        
            $query = $this->connection->executeQuery($sql);
    
            return $query->fetchAll(); 
    }
}
