<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Service\ApplicationManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        ApplicationManager    $applicationManager
        )
    {
        parent::__construct($registry, Notification::class);
        $this->application = $applicationManager->getApplicationActive();

    }

    public function findByIsViewFalseOrNull(): array
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.isView IS NULL OR n.isView = :false')
            ->andWhere('n.application = :application_id')
            ->setParameter('false', false)
            ->setParameter('application_id', $this->application->getId())
            ->getQuery()
            ->getResult();

        return $qb;
    }
}
