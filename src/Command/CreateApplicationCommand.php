<?php

namespace App\Command;

use App\Entity\Application;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateApplicationCommand extends Command
{
    protected static $defaultName = 'app:create-application';
    private $applicationRepository;
    private $em;

    /**
     * CreateUserCommand constructor.
     * @param ApplicationRepository $applicationRepository
     * @param EntityManagerInterface $em
     * @param string|null $name
     */
    public function __construct(
        ApplicationRepository $applicationRepository,
        EntityManagerInterface $em,
        string $name = null
    )
    {
        $this->adminRepository = $applicationRepository;
        $this->em = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Command to create application')
            ->setHelp('This command allows you to create an application...')
            ->addArgument('entreprise', InputArgument::REQUIRED, 'The name of the application.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nom = $input->getArgument('entreprise');
        if ($nom) {
            $applicationExist = $this->adminRepository->findOneBy([
                'entreprise' => $nom
            ]);
            if (!$applicationExist) {
                $application = new Application();
                $date = new \Datetime();
                $application->setEntreprise($nom);
                $application->setisActive(true);
                $this->em->persist($application);
                $this->em->flush();
                $io->success('Application has been created.');
                return Command::SUCCESS;
            }
        }
        $io->error('Application already saved.');
        return Command::FAILURE;
    }
}
