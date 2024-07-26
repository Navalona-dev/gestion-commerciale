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

   
}
