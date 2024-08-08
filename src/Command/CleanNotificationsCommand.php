<?php

namespace App\Command;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CleanNotificationsCommand extends Command
{
    protected static $defaultName = 'app:clean-notifications';

    private $notificationRepository;
    private $entityManager;

    public function __construct(NotificationRepository $notificationRepository, EntityManagerInterface $entityManager)
    {
        $this->notificationRepository = $notificationRepository;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Supprime les notifications avec isView égal à true.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notifications = $this->notificationRepository->findBy(['isView' => true]);

        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }

        $this->entityManager->flush();

        $output->writeln('Notifications supprimées avec succès.');
        return Command::SUCCESS;
    }
}
