<?php

namespace App\Repository;

use App\Service\DroitService;
use App\Entity\Compte;
use App\Entity\Affaire;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\Constraint\Count;
use App\Entity\Gomyclic\ParcoursEnCours;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Gomyclic\ParcoursIntervenant;
use App\Service\ApplicationManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Psr\Cache\InvalidArgumentException;
use Recurr\Recurrence;
use Recurr\RecurrenceCollection;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Affaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Affaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Affaire[]    findAll()
 * @method Affaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffaireRepository extends ServiceEntityRepository
{
    private $application;
    
    private $user;

    public function __construct(
        ManagerRegistry       $registry,
        TokenStorageInterface $tokenStorage,
        ApplicationManager    $applicationManager
    ) {
        parent::__construct($registry, Affaire::class);

        $this->application = $applicationManager->getApplicationActive();
        $this->user = (null == $tokenStorage->getToken()) ? null : $tokenStorage->getToken()->getUser();
        
    }

    // /**
    //  * @return Affaire[] Returns an array of Affaire objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Affaire
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
