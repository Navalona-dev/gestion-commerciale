<?php 

namespace App\Service;

use App\Service\ApplicationManager;
use App\Repository\ApplicationRepository;
use App\Repository\NotificationRepository;

class HeaderDataProvider
{
    private $applicationRepo;
    private $notificationRepo;
    private $application;

    public function __construct(
        ApplicationRepository $applicationRepo,
        NotificationRepository $notificationRepo,
        ApplicationManager $applicationManager
    )
    {
        $this->applicationRepo = $applicationRepo;
        $this->notificationRepo = $notificationRepo;
        $this->application = $applicationManager->getApplicationActive();

    }

    public function getHeaderData()
    {
        $applications = $this->applicationRepo->findAllApplication();
        
        $qb = $this->notificationRepo->createQueryBuilder('n');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('n.isView', ':false'),
            $qb->expr()->isNull('n.isView')
        ))
        ->andWhere('n.application = :application')
        ->setParameter('false', false)
        ->setParameter('application', $this->application)
        ->orderBy('n.dateCreation', 'DESC');

        $notifications = $qb->getQuery()->getResult();

        $countNotification = count($notifications);
        return [
            'applications' => $applications,
            'notifications' => $notifications,
            'countNotif' => $countNotification,
        ];
    }


}
