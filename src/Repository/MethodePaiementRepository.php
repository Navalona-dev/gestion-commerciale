<?php

namespace App\Repository;

use App\Entity\MethodePaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MethodePaiement>
 */
class MethodePaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MethodePaiement::class);
    }

    public function selectMethodeToday()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $qb = $this->createQueryBuilder('mp')
                    ->select('mp.id, mp.espece, mp.mVola, mp.orangeMoney, mp.airtelMoney')
                    ->where('mp.dateCreation >= :today')
                    ->andWhere('mp.dateCreation < :tomorrow')
                    ->setParameter('today', $today)
                    ->setParameter('tomorrow', $tomorrow)
                    ->getQuery()
                    ->getResult();
               return $qb;
    }
}
