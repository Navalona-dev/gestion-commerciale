<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Admin;
use Doctrine\ORM\ORMException;
use App\Repository\UserRepository;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';
    private $userRepository;
    private $em;
    protected $passwordEncoder;

    /**
     * CreateUserCommand constructor.
     * @param AdminRepository $userRepository
     * @param EntityManagerInterface $em
     * @param UserPasswordHasherInterface $passwordEncoder
     * @param string|null $name
     */
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordEncoder,
        string $name = null
    )
    {
        $this->adminRepository = $userRepository;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Command to create user')
            ->setHelp('This command allows you to create a user...')
            ->addArgument('nom', InputArgument::REQUIRED, 'The name of the user.')
            ->addArgument('prenom', InputArgument::REQUIRED, 'The firstname of the user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'User password plain password')
            ->addArgument('role', InputArgument::REQUIRED, 'User role (e.g. ROLE_ADMIN)');
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
        $nom = $input->getArgument('nom');
        $prenom = $input->getArgument('prenom');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');
        if ($nom && $prenom && $email && $password && $role && in_array($role, User::$ROLES, false)) {
            $userExist = $this->adminRepository->findOneBy([
                'email' => $email
            ]);
            if (!$userExist) {
                $user = new User();
                $date = new \Datetime();
                $user->setNom($nom);
                $user->setPrenom($prenom);
                $user->setEmail($email);
                $user->setDateModification($date);
                $roles = ['ROLE_ADMIN'];
                $roles [] = $role;
                $user->setRoles(array_unique($roles));
                $user->setPassword($this->passwordEncoder->hashPassword($user, $password));
                $this->em->persist($user);
                $this->em->flush();
                $io->success('Admin has been created.');
                return Command::SUCCESS;
            }
        }
        $io->error('Email already used or role invalid.');
        return Command::FAILURE;
    }
}
