<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Connection;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $connection;
    public function __construct(ManagerRegistry $registry, Connection $connection)
    {
        parent::__construct($registry, User::class);
        $this->connection = $connection;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function loadUserByUsername($usernameOrEmail)
    {
        return $this->_em->createQuery(
                'SELECT u
                FROM App\Entity\User u
                WHERE u.username = :query
                OR u.email = :query'
            )
            ->setParameter('query', $usernameOrEmail)
            ->getOneOrNullResult();
    }

    public function findAllUserInNiveauHierarchiqueEntite($niveauHierarchique = null, $entite = null, $region = null, $commune = null)
    {
        $sql = "SELECT * FROM utilisateur as u";

        if ($entite == "Apipa") {
            $sql .= " where niveau = '".$entite."'";
        }

        if ($entite == "Ministeriel") {
            $sql .= " where niveau = '".$entite."'";
        }

        if ($entite == "Regionale" || $entite == "SRAT") {
            $sql .= " where niveau = '".$entite."' and id_region = '".$region."'";
        }

        if ($entite == "Communale") {
            $sql .= " where niveau = '".$entite."' and id_commune = '".$commune."'";
        }
        $sql .= " and niveau_hierarchique = ".$niveauHierarchique."";
       
        $query = $this->connection->prepare($sql);
        $query->execute();

        $users = $query->fetchAll();

        if (sizeof($users) > 0) {
            return $users;
        }
        return false;
    }

    public function getAllUser($start = 0, $limit = 0, $search = "")
    {
        
        $sql = "SELECT nom, telephone, email, id, is_active FROM user as u";
        if ($search != "") {
            $sql .= "
            WHERE (u.nom ILIKE '%" . $search . "%' OR u.telephone ILIKE '%" . $search . "%' OR u.email ILIKE '%" . $search . "%') ";
        }
        $sql .= " GROUP BY u.id";
        $sql .= " ORDER BY u.nom DESC";

        $sql .= ((is_int($limit) AND ($limit > 0)) ? " LIMIT $limit" : "");
        $sql .= ((is_int($start) AND ($start > 0)) ? " OFFSET $start" : "");
     
        $query = $this->connection->executeQuery($sql);
        return $query->fetchAll();

    }

    public function countAll()
    {
        $sql = "SELECT COUNT(*) AS Total FROM utilisateur";
        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        $query->execute();
        return $query->fetchAll()[0]['total'];
    }

    public function getAllPrivilegeUser($idUser)
    {
        $sql = "SELECT privilege_id FROM privilege_user where user_id = ".$idUser."";
        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        $query->execute();
        $privilegesUser = $query->fetchAll();
        $tabIdPrivilege = [];
        if (sizeof($privilegesUser) > 0) {
            foreach($privilegesUser as $privilege) {
                if (!in_array($privilege['privilege_id'], $tabIdPrivilege)) {
                    array_push($tabIdPrivilege, $privilege['privilege_id']);
                }
            }

        }

        if (sizeof($tabIdPrivilege) > 0) {
            return $tabIdPrivilege;
        }

        return false;
    }

    public function deletePrivilegeUser($privilegeId, $idUser)
    {
        $sql = "DELETE FROM privilege_user where privilege_id = ".$privilegeId." and user_id = ".$idUser."";
        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        return $query->execute();
    }

    public function setActivationNullToken($user)
    {
        $sql = "UPDATE utilisateur set activation_token = null, is_active = true where id = ".$user->getId()."";

        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        return $query->execute();
    }

    public function setResetToken($user, $token)
    {
        $sql = "UPDATE utilisateur set reset_token = '".$token."' where id = ".$user->getId()."";

        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        return $query->execute();
    }

    public function insertPrivilegeUser($privilegeId, $idUser)
    {
        $sql = "INSERT into privilege_user (privilege_id, user_id) values  (".$privilegeId.", ".$idUser.")";
        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        return $query->execute();
    }

    public function insertDossierUser( $idUser, $dossierId)
    {
        if (!$this->checkIfUserHasDossier( $idUser, $dossierId)) {
            $sql = "INSERT into user_dossier (user_id, dossier_id) values  (".$idUser.", ".$dossierId.")";
            $conn = $this->_em->getConnection();
            $query = $conn->prepare($sql);
            return $query->execute();
        }
    }

    public function findAllUsersWhoWorkWithDossier($userId, $dossierId)
    {

        $sql = "SELECT * FROM user_dossier where user_id IN ".$userId." and dossier_id = ".$dossierId."";
        //$sql = "SELECT * FROM user_dossier where dossier_id = ".$dossierId."";
        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        $query->execute();
        $userDossier = $query->fetchAll();

        if (sizeof($userDossier) > 0) {
            return $userDossier[sizeof($userDossier) - 1]; // To do get last index
        }
        return false;

    }

    public function checkIfUserHasDossier( $idUser, $dossierId)
    {
        $sql = "SELECT * FROM user_dossier where user_id = ".$idUser." and dossier_id = ".$dossierId."";
        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        $query->execute();
        $userDossier = $query->fetchAll();
        if (sizeof($userDossier) > 0) {
            return true;
        }
        return false;
    }

    public function getAllEmailAdresses($entite, $region, $commune)
    {
        $sql = "SELECT email FROM utilisateur";
        if ($entite == "Ministeriel") {
            $sql .= " where niveau = '".$entite."'";
        }

        if ($entite == "Regionale" || $entite == "SRAT") {
            $sql .= " where niveau = '".$entite."' and id_region = '".$region."'";
        }

        if ($entite == "Communale") {
            $sql .= " where niveau = '".$entite."' and id_commune = '".$commune."'";
        }


        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        $query->execute();

        $emailAdresses = $query->fetchAll();

        if (sizeof($emailAdresses) > 0) {
            $allEmails = [];
            foreach($emailAdresses as $email)
            {
                array_push($allEmails, $email['email']);
            }

            return $allEmails;
        }
        return false;
    }

    public function getInfosClientDossier($idClient)
    {
        $sql = "SELECT * FROM utilisateur as u";
        $sql .= " LEFT JOIN dossier as d ON u.id = d.idclient";
        $sql .= " Where d.idclient = ".$idClient."";
        $conn = $this->_em->getConnection();
        $query = $conn->prepare($sql);
        $query->execute();
        $infoClient = $query->fetchAll();
        if (is_array($infoClient)) {
            return $infoClient[0];
        }
        return false;
    }
    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
