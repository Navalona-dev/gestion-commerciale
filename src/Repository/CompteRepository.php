<?php

namespace App\Repository;

use App\Service\DroitService;
use App\Entity\Compte;
use App\Entity\Gomyclic\Etapes;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

// use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Gomyclic\Parcours;
use App\Service\ApplicationManager;
use App\Entity\Gomyclic\Application;
use App\Entity\Gomyclic\ParcoursEnr;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Compte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Compte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Compte[]    findAll()
 * @method Compte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteRepository extends ServiceEntityRepository
{
    private $application;

    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager)
    {
        parent::__construct($registry, Compte::class);

        $this->application = $applicationManager->getApplicationActive();
        
    }

    public function findCompteClient($genre)
    {
        if (!is_array($genre)){
            if (!is_null($genre)){
                $genre = [$genre];
            }else{
                $genre = [];
            }
        }

        $query = $this->createQueryBuilder('c')
            ->where('c.application = :application')
            ->andWhere('c.genre IN (:genre)')
            ->setParameter('genre', $genre)
            ->setParameter('application', $this->application)
            ->orderBy('c.nom', 'ASC');

        return $query->getQuery()
            ->getResult();
    }

    public function searchCompte(
        $nom = null,
        $dateDu = null,
        $dateAu = null,
        $genre = null,
        $limit = null,
        $pg = 1,
        $etat = null
    ) {
        $query = $this->createQueryBuilder('c')
            //->join('c.application', 'a')
            ->where('c.application = :application')
            ->setParameter('application', $this->application);

        if (null != $nom) {
            $query = $query->andWhere("c.nom LIKE :nom OR c.adresse LIKE :nom OR
            c.telephone LIKE :nom OR c.email LIKE :nom")
                ->setParameter('nom', '%' . $nom . '%');
        }

        if (null != $dateDu && null != $dateAu) {
            $query = $query->andWhere('c.dateCreation >= :dateDu AND c.dateCreation <= :dateAu')
                ->setParameter('dateDu', $dateDu->format("Y-m-d"))
                ->setParameter('dateAu', $dateAu->format("Y-m-d"));
        } elseif (null != $dateDu && null == $dateAu) {
            $query = $query->andWhere('c.dateCreation >= :dateDu')
                ->setParameter('dateDu', $dateDu->format("Y-m-d"));
        } elseif (null == $dateDu && null != $dateAu) {
            $query = $query->andWhere('c.dateCreation <= :dateAu')
                ->setParameter('dateAu', $dateAu->format("Y-m-d"));
        }


        $query = $query->andWhere('c.genre = :genre')
                    ->setParameter('genre', $genre);

        if (null != $limit) {
            $query = $query->setMaxResults($limit)
                ->setFirstResult($pg);
        }

        //filtre par etat
        if (null != $etat) {

            if ($etat == "pre") {
                $query = $query->andWhere("c.etat LIKE :etat OR c.etat IS NULL");
            } else {
                $query = $query->andWhere("c.etat LIKE :etat");
            }

            $query = $query->setParameter('etat', '%' . $etat . '%');
        }

        $tabOrder = [
            0 => 'c.dateCreation',
            1 => 'c.nom',
            2 => 'c.email',
            3 => 'c.telephone',
            4 => 'c.adresse',

        ];
        
 
        $query->orderBy('c.dateCreation', 'DESC');
        
        $result = $query->getQuery()->getResult();
       
        return $result;
    }

    public function findDoublonValue($field)
    {
        $qb = $this->createQueryBuilder('c');

        $column = self::getColumnFromSelect($field);

        $qb->select(sprintf('c.%s,COUNT(c.%s) as doublonNom', $column, $column));
        $qb->where('c.application = :application AND (c.archive is null OR c.archive = 0)')->setParameter('application', $this->application);
        $qb->groupBy(sprintf('c.%s', $column));
        $qb->having(sprintf('COUNT(c.%s) > 1', $column));

        return $qb->getQuery()->getResult();
    }

    public function findDoublonEntities($field, $doublonValues, $genre, $limit, $pg = 1, $order = null, $count = false)
    {
        $column = self::getColumnFromSelect($field);
        $qb = $this->createQueryBuilder('compte');
        $qb->where(sprintf('compte.%s', $column) . ' IN (:values)')->setParameter('values', $doublonValues);
        $qb->andWhere('compte.application = :application AND (compte.archive is null OR compte.archive = 0)')->setParameter('application', $this->application);

        if ($column === 'email' or $column === 'telephone') {
            $qb->andWhere(sprintf('compte.%s IS NOT NULL', $column));
            $qb->andWhere($qb->expr()->gt($qb->expr()->length(sprintf('compte.%s', $column)), 0));
        }

        if (trim($genre) != '' and !is_null($genre)) {
            $qb->andWhere('compte.genre = :genre')->setParameter('genre', $genre);
        }

        if (!$count and null != $limit) {
            $qb->setMaxResults($limit)->setFirstResult($pg);
        }

        $qb->orderBy(sprintf('compte.%s', $column), 'ASC');

        if ($count) {
            $qb->select('COUNT(compte.id) as nbTotal');
            return $qb->getQuery()->getSingleResult();
        }

        return $qb->getQuery()->getResult();
    }

    public function lastNumero()
    {
        $query = $this->createQueryBuilder('c');

        $query =
            //$query->join('c.application', 'ap')
            $query->where('c.application = :application')
            ->andWhere('c.numero is not null AND (c.archive is null OR c.archive = 0)')
            ->setParameter('application', $this->application->getId())
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1);

        $results = $query->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();

        $tabNumero = [];

        if ($results) {
            foreach ($results as $result) {
                $tabNumero[] = intval($result->getNumero());
            }

            arsort($tabNumero);
            $tabNumero = array_merge($tabNumero, []);
        }

        $lastNumero = 0;

        if (isset($tabNumero[0])) {
            $lastNumero = $tabNumero[0];
        }

        return $lastNumero;
    }

    public function lastCompteApplication($genre, $application = null)
    {
        $query = $this->createQueryBuilder('c');

        $application = (null != $application) ? $application : $this->application;

        $query = //$query->join('c.application', 'ap')
            $query->where('c.application = :application AND c.genre = :genre')
            ->andWhere('c.numero is not null AND (c.archive is null OR c.archive = 0)')
            ->setParameter('application', $application->getId())
            ->setParameter('genre', $genre)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1);

        $results = $query->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();

        $tabNumero = [];

        if ($results) {
            foreach ($results as $result) {
                $tabNumero[] = intval($result->getNumero());
            }

            arsort($tabNumero);
            $tabNumero = array_merge($tabNumero, []);
        }

        $lastNumero = 0;

        if (isset($tabNumero[0])) {
            $lastNumero = $tabNumero[0];
        }

        return $lastNumero;
    }


    public function findCompteArray(
        $application,
        $typeCompte = null,
        $limit = null,
        $pg = 1,
        $nom = null,
        $idCompte = null,
        $collaborateur = null
    ) {
        $query = $this->createQueryBuilder('c');
        $query = $query->select('c.id, c.genre, c.nom, c.telephone')
            //->join('c.application', 'ap')
            ->where('c.application = :application AND (c.archive is null OR c.archive = 0)')
            ->setParameter('application', $application);
        $collaborateurDroit = $this->droitService->getListUtilisateurCollaborateurAllowed(1, null);

        if (null != $collaborateur) {
            $query = $query->join('c.commerciale', 'cc')
                ->andWhere('cc.utilisateur in (:collaborateur)')
                ->setParameter('collaborateur', $collaborateur);
        } else {
            if ($collaborateurDroit) {
                $query = $query->join('c.commerciale', 'cc')
                    ->andWhere('cc.id in (:collaborateur)')
                    ->setParameter('collaborateur', $collaborateurDroit);
            }
        }
        if (null != $nom) {
            $query = $query->andWhere("c.nom LIKE :nom OR c.boite_postale LIKE :nom OR c.ville LIKE :nom OR c.adresse LIKE :nom OR
            c.telephone LIKE :nom OR c.email LIKE :nom OR DATE_FORMAT(c.dateCreation, '%d/%m/%Y') LIKE :nom")
                ->setParameter('nom', '%' . $nom . '%');
        }

        if (null != $typeCompte) {
            if ($typeCompte == 1 && $application->getId() != 8) {
                $query = $query->andWhere('c.genre = :typeClient OR c.genre = 0 OR c.genre = 5 OR c.genre = 6')
                    ->setParameter('typeClient', $typeCompte);
            } else {
                $query = $query->andWhere('c.genre = :typeClient')
                    ->setParameter('typeClient', $typeCompte);
            }
        }

        if (null != $idCompte) {
            $query = $query->andWhere("c.id = :idCompte ")
                ->setParameter('idCompte', $idCompte);
        }

        $query->orderBy('c.nom', 'ASC');

        if (null != $limit) {
            $query = $query->setMaxResults($limit)
                ->setFirstResult($pg);
        }

        $result = $query->getQuery()
            ->getArrayResult();

        return $result;
    }


    public function findCompteArrayV2($entityManager, $applicationId, $genre = null)
    {

        if (null != $genre || $genre == 0) {

            if ($applicationId == 54 or $applicationId == 86 or $applicationId == 118) {
                $sql = "SELECT nom, id, genre, telephone FROM Compte  WHERE application_id = :applicationId AND (archive is null OR archive = 0)";
            } else {
                if ($genre == 1) {
                    $sql = "SELECT nom, id, genre, telephone FROM Compte  WHERE application_id = :applicationId AND (archive is null OR archive = 0) AND (genre = " . $genre . " OR genre = 0 OR genre = 5 OR genre = 6)";
                } else {
                    $sql = "SELECT nom, id, genre, telephone FROM Compte  WHERE application_id = :applicationId AND (archive is null OR archive = 0) AND genre = " . $genre;
                }
            }

            $conn = $entityManager->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('applicationId' => $applicationId));
        }


        return $stmt->fetchAll();
    }

    public function getCompteIdNom($entityManager, $applicationId, $nom = null, $genre = 'tout')
    {
        switch ($genre) {
            case '0':
                $sql = "SELECT nom as id, nom as text FROM Compte  WHERE application_id = :applicationId AND nom LIKE :nom AND genre = 0 AND (archive is null OR archive = 0)";
                $conn = $entityManager->getConnection();
                $stmt = $conn->prepare($sql);
                $stmt->execute(array('applicationId' => $applicationId, 'nom' => '%' . $nom . '%'));
            case '1':
                $sql = "SELECT nom as id, nom as text FROM Compte  WHERE application_id = :applicationId AND nom LIKE :nom AND genre = 1 AND (archive is null OR archive = 0)";
                $conn = $entityManager->getConnection();
                $stmt = $conn->prepare($sql);
                $stmt->execute(array('applicationId' => $applicationId, 'nom' => '%' . $nom . '%'));
                break;
            case '2':
                $sql = "SELECT nom as id, nom as text FROM Compte  WHERE application_id = :applicationId AND nom LIKE :nom AND genre = 2 AND (archive is null OR archive = 0)";
                $conn = $entityManager->getConnection();
                $stmt = $conn->prepare($sql);
                $stmt->execute(array('applicationId' => $applicationId, 'nom' => '%' . $nom . '%'));
                break;

            default:
                $sql = "SELECT nom as id, nom as text FROM Compte  WHERE application_id = :applicationId AND nom LIKE :nom AND (archive is null OR archive = 0)";
                $conn = $entityManager->getConnection();
                $stmt = $conn->prepare($sql);
                $stmt->execute(array('applicationId' => $applicationId, 'nom' => '%' . $nom . '%'));
                break;
        }
        return $stmt->fetchAll();
    }

    public function findLastAdded($genre, $maxResult)
    {
        $collaborateurDroit = $this->droitService->getListUtilisateurCollaborateurAllowed(1, null);
        $query = $this->createQueryBuilder('c')
            ->select('c.id, c.nom, c.dateCreation')
            //->join('c.application', 'a')
            ->where('c.application = :application AND (c.archive is null OR c.archive = 0)')
            ->andWhere('c.genre = :genre')
            ->setMaxResults($maxResult)
            ->setParameter('genre', $genre)
            ->setParameter('application', $this->application);
        if ($collaborateurDroit || count($collaborateurDroit) > 0) {
            $query = $query->join('c.commerciale', 'cc')
                ->andWhere('cc.id in (:collaborateur)')
                ->setParameter('collaborateur', $collaborateurDroit);
        }
        $result = $query->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return $result;
    }

    public function getAllCp()
    {
        $query = $this->createQueryBuilder('c');
        $query = $query->select('c.code_postal')
            ->where('c.application = :application AND (c.archive is null OR c.archive = 0)')
            ->setParameter('application', $this->application);


        $query->groupBy('c.code_postal');
        $query->orderBy('c.code_postal', 'ASC');

        $result = $query->getQuery()
            ->getArrayResult();

        return $result;
    }

    public function countDuplicateName($currentId, $name)
    {
        $qb = $this->createQueryBuilder('compte');
        $qb->select('COUNT(compte.id) as nbDuplicate');

        if ($currentId) {
            $qb->where($qb->expr()->neq('compte.id', $currentId));
        }

        $qb->andWhere('compte.nom = :name AND (compte.archive is null OR compte.archive = 0)')->setParameter('name', $name);
        $qb->andWhere('compte.application = :application')->setParameter('application', $this->application);

        return $qb->getQuery()->getSingleScalarResult();
    }

    static function getColumnFromSelect($field)
    {
        switch ($field) {
            case 'name':
                return 'nom';
            case 'email':
                return 'email';
            case 'phone':
                return 'telephone';
            default:
                return 'nom';
        }
    }


    public function findCompteApac(Application $application, $email)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.email = :email AND c.application = :application')
            ->andWhere('c.idWp is not null AND (c.archive is null OR c.archive = 0)')
            ->setParameter('email', $email)
            ->setParameter('application', $application)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getComptesByInYear($year = null)
    {
        $year = (null != $year)?$year:(new \DateTime())->format("Y");
        
        $dateDebut = new \DateTime($year."-01-01");
        $dateFin = new \DateTime($year."-12-31");
        $query = $this->createQueryBuilder('c');
        $query = $query->andWhere('c.application = :application')
            ->andWhere("DATE_FORMAT(c.dateCreation, '%Y-%m-%d') >= '" . $dateDebut->format("Y-m-d") . "' and DATE_FORMAT(c.dateCreation, '%Y-%m-%d') <= '" . $dateFin->format("Y-m-d") . "' ")
            ->andWhere('c.archive is null OR c.archive = 0')
            ->setParameter('application', $this->application);

        return
            $query->getQuery()
            ->getResult();
    }

    public function getComptesByCommercial($commerciale = null, $tagSource = null, $year = null)
    {
        $query = $this->createQueryBuilder('c');
        $query = $query->select('COUNT(c.id) as nombreCompte, MONTH(c.dateCreation) as month, GROUP_CONCAT(c.id) as compteIds')
            ->join('c.commerciale', 'commerciale')
            ->join('c.tags', 'tag')
            ->andWhere('c.application = :application')
            ->andWhere('commerciale.id = :commercial AND tag.id =:tag AND (c.archive is null OR c.archive = 0)');
        if (null != $year) {
            $query->andWhere('YEAR(c.dateCreation) =:annee')
                ->setParameter('annee', $year);
        }

        $query->setParameter('commercial', $commerciale)
            ->setParameter('tag', $tagSource)
            ->setParameter('application', $this->application);

        return $query->groupBy('month')
            ->getQuery()
            ->getResult();

        /*$sql = "SELECT COUNT(compte.id) as nbCompte from Compte compte";
        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();*/
    }

    public function searchCompteRawSql(
        $genre = null,
        $nom = null,
        $dateDu = null,
        $dateAu = null,
        $etat = null,
        $limit = null,
        $pg = 1,
        $order = null,
        $isCount = false,
    ) {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $joins = $conditions = $sqlLimit = "";
        $parameters = [];
        $parameterType = [];

        if (!$isCount) {
            $select = "SELECT compte.* FROM compte compte";
        } else {
            $select = "SELECT COUNT(compte.id) as nbCompte, compte.id as idCompte, compte.email as emailCompte, compte.nom as nomCompte, compte.telephone as telephoneCompte FROM compte compte";
        }
        $conditions = self::conditionConcatener($conditions, "compte.application_id = :applicationId");


        $parameters['applicationId'] = $this->application->getId();
        $parameterType['applicationId'] = ParameterType::INTEGER;

        if (null != $nom) {
            $conditions = self::conditionConcatener($conditions, '(compte.nom like :nom or compte.adresse like :nom or
                           compte.telephone like :nom or compte.email like :nom)');
            $parameters['nom'] = '%' . trim($nom) . '%';
            $parameterType['nom'] = ParameterType::STRING;
        }

        if (null != $dateDu && null != $dateAu) {
            $conditions = self::conditionConcatener($conditions, "compte.dateCreation >= :dateDu");
            $conditions = self::conditionConcatener($conditions, "compte.dateCreation <= :dateAu");

            $parameters['dateDu'] = $dateDu->format("Y-m-d");
            $parameterType['dateDu'] = ParameterType::STRING;
            $parameters['dateAu'] = $dateAu->format("Y-m-d");
            $parameterType['dateAu'] = ParameterType::STRING;
        } elseif (null != $dateDu && null == $dateAu) {
            $conditions = self::conditionConcatener($conditions, "compte.dateCreation >= :dateDu");
            $parameters['dateDu'] = $dateDu->format("Y-m-d");
            $parameterType['dateDu'] = ParameterType::STRING;
        } elseif (null == $dateDu && null != $dateAu) {
            $conditions = self::conditionConcatener($conditions, "compte.dateCreation <= :dateAu");
            $parameters['dateAu'] = $dateAu->format("Y-m-d");
            $parameterType['dateAu'] = ParameterType::STRING;
        }

        if ($genre != "") {
            $conditions = self::conditionConcatener($conditions, "compte.genre = :typeClient");
            $parameters['typeClient'] = $genre;
            $parameterType['typeClient'] = ParameterType::INTEGER;
            
        }

        if (null != $limit) {
            $sqlLimit = ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($pg);
            //$parameters['sqlLimit'] =  intval($limit);
            //$parameters['pg'] =  intval($pg);
        }
        
        //filtre par etat
        if (null != $etat) {

            $conditions = self::conditionConcatener($conditions, "compte.etat LIKE :etat");

            $parameters['etat'] = "%" . $etat . "%";
            $parameterType["etat"] = ParameterType::STRING;
        }


        $conditions .= ' group by compte.id ';

        
        $tabOrder = [
            0 => 'compte.dateCreation',
            1 => 'compte.nom',
            2 => 'compte.email',
            3 => 'compte.telephone',
            4 => 'compte.adresse',

        ];
        


        if (isset($order[0]['column'])) {

            if (isset($tabOrder[$order[0]['column']])) {

                $intOrder = intval($order[0]['column']);

                if ($intOrder == 0) {
                    if ($order[0]['dir'] == "asc") {
                        $order[0]['dir'] = str_replace("asc", "desc", $order[0]['dir']);
                    } else {
                        $order[0]['dir'] = "asc";
                    }
                }

                $conditions .= ' ORDER BY ' . $tabOrder[$intOrder] . ' ' . strtoupper($order[0]['dir']);
            } else {
                $conditions .= ' ORDER BY compte.dateCreation DESC';
            }
        } else {
            $conditions .= ' ORDER BY compte.dateCreation DESC';
        }

        $rawSql = $select . ' ' . $joins . ' WHERE ' . $conditions . ' ' . ($sqlLimit ?? '');
      
        $connection = $this->getEntityManager()->getConnection();
        try {
            //dd($connection->fetchOne('SELECT COUNT(*) AS nbCompte FROM (' . $rawSql . ') AS ROWS_LINE ', $parameters, $parameterType), $rawSql, $parameters, $parameterType);
            if ($isCount) {
                return $connection->fetchOne('SELECT COUNT(*) AS nbCompte FROM (' . $rawSql . ') AS ROWS_LINE ', $parameters, $parameterType);
                
            }

            return $connection->fetchAllAssociative($rawSql, $parameters, $parameterType);
        } catch (\Exception $exception) {
        }
        if ($isCount) {
            return 0;
        }
        //dd("ici");
        return [];
    }

    private static function conditionConcatener($currentCondition, $condition, $operator = "AND"): string
    {
        if ($currentCondition != "" and $currentCondition != null) {
            return $currentCondition . " " . $operator . " " . $condition;
        }

        return $condition;
    }

    public function findDoublonDeleteValue($field, $isCount = false, $grouped = false)
    {
        $em = $this->getEntityManager();

        if ($field == 'nom_email') {

            $sql = 'SELECT COUNT(DISTINCT Compte.id) as nbItem,GROUP_CONCAT(Compte.id) as compteIds, CONCAT(nom,"|",email) as nomEmail
                    FROM Compte
                    WHERE application_id = ' . $this->application->getId() . '
                    and nom is not null
                    and nom <> ""
                    and email is not null
                    and email <> ""
                    and COALESCE(archive,0) <> 1
                    GROUP BY nomEmail
                    HAVING nbItem > 1';
        } elseif ($field == 'nom_phone') {
            $sql = 'SELECT COUNT(DISTINCT Compte.id) as nbItem,GROUP_CONCAT(Compte.id) as compteIds,CONCAT(nom,"|",telephone) as nomTelephone
                    FROM Compte
                    where application_id = ' . $this->application->getId() . '
                    and nom is not null
                    and nom <> ""
                    and telephone is not null
                    and telephone <> ""
                    and COALESCE(archive,0) <> 1
                    GROUP BY nomTelephone
                    having nbItem > 1';
        } elseif ($field == 'nom_postal') {
            $sql = 'SELECT COUNT(DISTINCT Compte.id) as nbItem,GROUP_CONCAT(Compte.id) as compteIds, CONCAT(nom,"|",code_postal) as nomPostal
                    FROM Compte
                    where application_id = ' . $this->application->getId() . '
                    and nom is not null
                    and nom <> ""
                    and code_postal is not null
                    and code_postal <> ""
                    and COALESCE(archive,0) <> 1
                    GROUP BY nomPostal
                    having nbItem > 1';
        } else {
            $sql = 'SELECT COUNT(DISTINCT Compte.id) as nbItem, GROUP_CONCAT(DISTINCT Compte.id) as compteIds, CONCAT(Compte.nom,Utilisateur.nom) as nomCompteAndNomUtilisateur
                    FROM Compte
                    join compte_utilisateur ON compte_utilisateur.compte_id = Compte.id
                    join Utilisateur ON Utilisateur.id = compte_utilisateur.utilisateur_id
                    where Compte.application_id = ' . $this->application->getId() . '
                    and Compte.nom is not null
                    and Compte.nom <>""
                    and Utilisateur.nom is not null
                    and Utilisateur.nom <> ""
                    and COALESCE(archive,0) <> 1
                    group by nomCompteAndNomUtilisateur
                    having nbItem > 1';
        }

        if ($isCount) {
            $sql = 'SELECT SUM(nbItem) as total FROM (' . $sql . ') As sqlQuery';
            $stmt = $em->getConnection()->prepare($sql);
            return $stmt->executeQuery()->fetchOne() ?? 0;
        } elseif (!$grouped) {
            $stmt = $em->getConnection()->prepare($sql);
            $results = $stmt->executeQuery()->fetchAllAssociative();

            $ids = [];

            foreach ($results as $result) {
                $tmp = explode(',', $result['compteIds']);
                $ids = array_merge($ids, $tmp);
            }

            $ids = array_unique($ids);

            return ['ids' => $ids, 'count' => count($ids)];
        }

        $stmt = $em->getConnection()->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function findUnarchivedCompte($nom = null, $ids = [], $secondField = null, $secondFieldValue = null)
    {
        $qb = $this->createQueryBuilder('compte');

        if ($nom) {
            $qb->where('compte.nom = :nom')->setParameter('nom', $nom);
        }

        if (count($ids) > 0) {
            $qb->andWhere('compte.id in (:ids)')->setParameter('ids', $ids);
        }

        if ($secondField and $secondFieldValue) {
            $qb->andWhere(sprintf('compte.%s = :secondFieldValue', $secondField))->setParameter('secondFieldValue', $secondFieldValue);
        }

        $qb->andWhere('(compte.archive IS NULL or compte.archive =0)');
        $qb->andWhere('compte.application = :applicationId')->setParameter('applicationId', $this->application->getId());
        $qb->orderBy('compte.dateCreation', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findUnarchivedCompteByDoublonValues($field, $secondField, $doublonIds, $limit, $pg = 1)
    {

        $qb = $this->createQueryBuilder('compte');
        $qb->orderBy('compte.nom', 'ASC');

        if ($field == 'nom_type') {
            $qb->addOrderBy('utilisateur.nom', 'ASC');
            $qb = $qb->join('compte.utilisateur', 'utilisateur');
        } else {
            $qb->addOrderBy(sprintf('compte.%s', $secondField), 'ASC');
        }

        if (count($doublonIds) > 0) {
            $qb->andWhere('compte.id in (:ids)')->setParameter('ids', $doublonIds);
        } else {
            return [];
        }

        //$qb->andWhere('compte.application = :application')->setParameter('application', $this->application->getId());
        //$qb->andWhere('(compte.archive IS NULL OR compte.archive = 0)');

        if (null != $limit) {
            $qb->setMaxResults($limit)->setFirstResult($pg);
        }

        return $qb->getQuery()->getResult();
    }

    public function findCompteStatistique($application)
    {
        $query = $this->createQueryBuilder('c');

        $query = $query->select('c.genre, COUNT(c.id) as count')
            ->where('c.application = :application AND (c.archive is null OR c.archive = 0)')
            ->setParameter('application', $application);
        $collaborateurDroit = $this->droitService->getListUtilisateurCollaborateurAllowed(1, null);

        if ($collaborateurDroit) {
            $query = $query->join('c.commerciale', 'cc')
                ->andWhere('cc.id in (:collaborateur)')
                ->setParameter('collaborateur', $collaborateurDroit);
        }

        $query->groupBy('c.genre');

        return $query->getQuery()->getArrayResult();
    }

    public function searchCompteWithKey($keyword, $application, $limit = 20)
    {
        $query = $this->createQueryBuilder('c');

        $query = $query->select('c.id')
            ->addSelect('c.nom');

        $query = $query->where('c.application = :application')
            ->setParameter('application', $application);

        $query = $query->andWhere($query->expr()->like('c.nom', "'%" . $keyword . "%'"));

        $query = $query->orderBy('c.nom', 'ASC');

        $query = $query->setMaxResults($limit);

        return $query->getQuery()->getArrayResult();
    }

    public function getPaginatedCompte(
        $typeCompte = null,
        $page = 1,
        $limit = 25,
        $nom = null,
        $idCompte = null,
        $collaborateur = null,
        $categorie = null
    ) {

        if (!$page) {
            $page = 1;
        }

        $query = $this->createQueryBuilder('c');
        $query = $query->where('c.application = :application AND (c.archive is null OR c.archive = 0)')
            ->setParameter('application', $this->application);
        $collaborateurDroit = $this->droitService->getListUtilisateurCollaborateurAllowed(1, null);

        if (null != $collaborateur) {
            $query = $query->join('c.commerciale', 'cc')
                ->andWhere('cc.utilisateur in (:collaborateur)')
                ->setParameter('collaborateur', $collaborateur);
        } else {
            if ($collaborateurDroit) {
                $query = $query->join('c.commerciale', 'cc')
                    ->andWhere('cc.id in (:collaborateur)')
                    ->setParameter('collaborateur', $collaborateurDroit);
            }
        }
        if (null != $nom) {
            $query = $query->andWhere("c.nom LIKE :nom")
                ->setParameter('nom', '%' . $nom . '%');
        }

        if (null != $typeCompte) {
            if ($typeCompte == 1 && $this->application->getId() != 8) {
                $query = $query->andWhere('c.genre = :typeClient OR c.genre = 0 OR c.genre = 5 OR c.genre = 6')
                    ->setParameter('typeClient', $typeCompte);
            } else {
                $query = $query->andWhere('c.genre = :typeClient')
                    ->setParameter('typeClient', $typeCompte);
            }
        }

        if (null != $idCompte) {
            $query = $query->andWhere("c.id = :idCompte ")
                ->setParameter('idCompte', $idCompte);
        }

        if (null != $categorie) {
            $query->join('c.champPlus', 'champPlus');
            $query->join('champPlus.champAppli', 'champAppli');
            $query->andWhere('champAppli.nom = :nomCat')->setParameter('nomCat', 'cat_egorie');
            $query->andWhere('champPlus.valeur = :cat')->setParameter('cat', $categorie);
        }

        $query->orderBy('c.nom', 'ASC');

        $paginator = new Paginator($query);

        if ($limit != null and $page != null) {
            $paginator->getQuery()->setMaxResults($limit)
                ->setFirstResult($limit * ($page - 1));
        }

        return $paginator;
    }

    public function findCompteAppToMap(int $appID, EntityManagerInterface $em): array
    {
        try {
            $sql = "SELECT c.id FROM `Compte` as c WHERE  c.application_id = :app_id AND c.adresse is not null AND (c.longitude is null or c.longitude = '' or c.latitude is null or c.latitude = '')";

            $conn = $em->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute(array('app_id' => $appID));

            return $stmt->fetchAll();
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param $mandantId
     * @param $numIdentification
     * @param $paginate
     * @param $limit
     * @param $page
     * @return array|int|mixed|string
     * @throws \Exception
     */
    public function getDistributeurFromMandantDistributeur($mandantId, $numIdentification = null, $paginate = false, $limit = 25, $page = 1)
    {
        $qb = $this->createQueryBuilder('compte');
        $qb->select('distributeur.id as id, distributeur.nom as nom');
        $qb->join('compte.application', 'application');
        $qb->join('compte.mandantDistributeursAsDistributeurs', 'mandantDistributeursAsDistributeurs');
        $qb->join('mandantDistributeursAsDistributeurs.distributeur', 'distributeur');
        $qb->where('mandantDistributeursAsDistributeurs.mandant = :mandantId')
            ->setParameter('mandantId', $mandantId);

        $qb->andWhere('application.id = :application')->setParameter('application', $this->application);

        if ($numIdentification) {
            $qb->andWhere('mandantDistributeursAsDistributeurs.numIdentification = :numIdentification')
                ->setParameter('numIdentification', $numIdentification);
        }

        if ($paginate) {
            $paginator = new Paginator($qb);

            if ($limit != null and $page != null) {
                $paginator->getQuery()->setMaxResults($limit)
                    ->setFirstResult($limit * ($page - 1));
            }

            return (array)$paginator->getIterator();
        }

        return $qb->getQuery()->getResult();
    }

    public function getMandantFromMandantDistributeur($distributeurId, $numIdentification = null, $paginate = false, $limit = 25, $page = 1)
    {
        $qb = $this->createQueryBuilder('compte');
        $qb->select('mandant.id as id, mandant.nom as nom');
        $qb->join('compte.application', 'application');
        $qb->join('compte.mandantDistributeursAsDistributeurs', 'mandantDistributeursAsDistributeurs');
        $qb->join('mandantDistributeursAsDistributeurs.mandant', 'mandant');
        $qb->where('mandantDistributeursAsDistributeurs.distributeur = :distributeurId')
            ->setParameter('distributeurId', $distributeurId);

        $qb->andWhere('application.id = :application')->setParameter('application', $this->application);

        if ($numIdentification) {
            $qb->andWhere('md.numIdentification = :numIdentification')
                ->setParameter('numIdentification', $numIdentification);
        }

        if ($paginate) {
            $paginator = new Paginator($qb);

            if ($limit != null and $page != null) {
                $paginator->getQuery()->setMaxResults($limit)
                    ->setFirstResult($limit * ($page - 1));
            }

            return (array)$paginator->getIterator();
        }

        return $qb->getQuery()->getResult();
    }

    public function getNotSynchroned(Application $application): array
    {
        try {
            $query = $this->createQueryBuilder('c');
            $query = $query->where('c.application = :application')
                ->setParameter('application', $application)
                ->andWhere('c.idWp IS NULL');

            return $query->getQuery()->getResult();
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function getDestInvoice($compteId)
    {
        $em = $this->getEntityManager();

        try {
            $sql = "SELECT u.mail FROM `Compte` c
                JOIN compte_utilisateur cu
                ON c.id = cu.compte_id
                JOIN Utilisateur u 
                ON u.id = cu.utilisateur_id
                WHERE c.id = " . $compteId . "
                AND u.isDestFacture = 1
            ";

            $conn = $em->getConnection();
            $stmt = $conn->prepare($sql);
            $result = $stmt->executeQuery();

            return $result->fetchAssociative()[0];
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function getPaginatedV2Compte(
        $em,
        $page = 1,
        $limit = 25,
        $nom = null,
        $idCompte = null,
        $formationOf = null
    ) {
        if (!$page) {
            $page = 1;
        }

        $offset = $limit * ($page - 1);
        $params = array();

        try {
            if($formationOf){
                $sql = "SELECT c.id, c.nom FROM `FormationOf` as c WHERE  c.application_id = " . $this->application->getId() ;
            }else {
                $sql = "SELECT c.id, c.nom FROM `Compte` as c WHERE  c.application_id = " . $this->application->getId() . " AND (c.archive is null OR c.archive = 0)";
            }

            $params['app_id'] = $this->application->getId();

            if (null != $nom) {
                $sql .= " AND c.nom LIKE '%" . $nom . "%'";
                $params["nom"] = '%' . ($formationOf)?strip_tags($nom):$nom . '%';
            }

            if (null != $idCompte) {
                $sql .= " AND c.id = " . $idCompte;
                $params["id"] = $idCompte;
            }

            $sql .= " ORDER BY c.nom ASC LIMIT " . $limit . " OFFSET " . $offset;
            $params["offset"] = $offset;
            $params["limit"] = $limit;

            $conn = $em->getConnection();
            $stmt = $conn->prepare($sql);
            // $stmt->execute($params);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function getPaginatedV2CompteCount(
        $em,
        $nom = null,
        $idCompte = null,
        $formationOf = null
    ) {
        $params = array();

        try {
            if($formationOf){
                $sql = "SELECT COUNT(*) as count FROM `FormationOf` as c WHERE  c.application_id = " . $this->application->getId() ;
            }else {
                $sql = "SELECT COUNT(*) as count FROM `Compte` as c WHERE  c.application_id = " . $this->application->getId() . " AND (c.archive is null OR c.archive = 0)";
            }

            $params['app_id'] = $this->application->getId();

            if (null != $nom) {
                $sql .= " AND c.nom LIKE '%" . $nom . "%'";
                $params["nom"] = '%' . $nom . '%';
            }

            if (null != $idCompte) {
                $sql .= " AND c.id = " . $idCompte;
                $params["id"] = $idCompte;
            }

            $conn = $em->getConnection();
            $stmt = $conn->prepare($sql);
            // $stmt->execute($params);
            
            $stmt->execute();

            
            return $stmt->fetch()["count"];
        } catch (\Exception $exception) {
            return 0;
        }
    }

    public function findNameCompteByApplication($application): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom') 
            ->innerJoin('c.application', 'a') 
            ->andWhere('a.id = :application') 
            ->andWhere('c.genre = :genre') 
            ->setParameter('application', $application)
            ->setParameter('genre', 2)
            ->groupBy('c.nom', 'c.application', 'c.genre') 
            ->orderBy('c.nom', 'ASC') 
            ->getQuery()
            ->getResult();
    }

     // Nombre de client d'aujourd'hui
     public function countComptesToday($genre = null)
     {
         $today = new \DateTime();
         $today->setTime(0, 0, 0);
         $tomorrow = clone $today;
         $tomorrow->modify('+1 day');

         $qb = $this->createQueryBuilder('c')
                     ->select('COUNT(c.id)')
                     ->where('c.dateCreation >= :today')
                     ->andWhere('c.dateCreation < :tomorrow')
                     ->andWhere('c.genre = :genre')
                     ->andWhere('c.application = :application_id')
                     ->setParameter('today', $today)
                     ->setParameter('tomorrow', $tomorrow)
                     ->setParameter('genre', $genre)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();

                return $qb;
     }

     // Nombre de client d'hier
     public function countComptesYesterday($genre = null)
     {
         $yesterday = new \DateTime();
         $yesterday->setTime(0, 0, 0);
         $yesterday->modify('-1 day');
         $today = clone $yesterday;
         $today->modify('+1 day');
 
         $qb = $this->createQueryBuilder('c')
                     ->select('COUNT(c.id)')
                     ->where('c.dateCreation >= :yesterday')
                     ->andWhere('c.dateCreation < :today')
                     ->andWhere('c.genre = :genre')
                     ->andWhere('c.application = :application_id')
                     ->setParameter('yesterday', $yesterday)
                     ->setParameter('today', $today)
                     ->setParameter('genre', $genre)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

     // Nombre de client cette semaine
     public function countComptesThisWeek($genre = null)
     {
         $startOfWeek = new \DateTime();
         $startOfWeek->setISODate((int)$startOfWeek->format('o'), (int)$startOfWeek->format('W'));
         $startOfWeek->setTime(0, 0, 0);
 
         $endOfWeek = clone $startOfWeek;
         $endOfWeek->modify('+7 days');
 
         $qb = $this->createQueryBuilder('c')
                     ->select('COUNT(c.id)')
                     ->where('c.dateCreation >= :start_of_week')
                     ->andWhere('c.dateCreation < :end_of_week')
                     ->andWhere('c.application = :application_id')
                     ->andWhere('c.genre = :genre')
                     ->setParameter('start_of_week', $startOfWeek)
                     ->setParameter('end_of_week', $endOfWeek)
                     ->setParameter('genre', $genre)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de client semaine dernière
      public function countComptesLastWeek($genre = null)
      {
          $startOfLastWeek = new \DateTime();
          $startOfLastWeek->setISODate((int)$startOfLastWeek->format('o'), (int)$startOfLastWeek->format('W') - 1);
          $startOfLastWeek->setTime(0, 0, 0);
  
          $endOfLastWeek = clone $startOfLastWeek;
          $endOfLastWeek->modify('+7 days');
  
          $qb = $this->createQueryBuilder('c')
                      ->select('COUNT(c.id)')
                      ->where('c.dateCreation >= :start_of_last_week')
                      ->andWhere('c.dateCreation < :end_of_last_week')
                      ->andWhere('c.application = :application_id')
                      ->andWhere('c.genre = :genre')
                      ->setParameter('start_of_last_week', $startOfLastWeek)
                      ->setParameter('end_of_last_week', $endOfLastWeek)
                      ->setParameter('genre', $genre)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

      // Nombre de client ce mois-ci
     public function countComptesThisMonth($genre = null)
     {
         $startOfMonth = new \DateTime('first day of this month');
         $startOfMonth->setTime(0, 0, 0);
 
         $endOfMonth = new \DateTime('first day of next month');
         $endOfMonth->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('c')
                     ->select('COUNT(c.id)')
                     ->where('c.dateCreation >= :start_of_month')
                     ->andWhere('c.dateCreation < :end_of_month')
                     ->andWhere('c.genre = :genre')
                     ->andWhere('c.application = :application_id')
                     ->setParameter('start_of_month', $startOfMonth)
                     ->setParameter('end_of_month', $endOfMonth)
                     ->setParameter('genre', $genre)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de client mois dernier
      public function countComptesLastMonth($statut = null)
      {
          $startOfLastMonth = new \DateTime('first day of last month');
          $startOfLastMonth->setTime(0, 0, 0);
  
          $endOfLastMonth = new \DateTime('first day of this month');
          $endOfLastMonth->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('c')
                      ->select('COUNT(c.id)')
                      ->where('c.dateCreation >= :start_of_last_month')
                      ->andWhere('c.dateCreation < :end_of_last_month')
                      ->andWhere('c.statut = :statut')
                      ->andWhere('c.application = :application_id')
                      ->setParameter('start_of_last_month', $startOfLastMonth)
                      ->setParameter('end_of_last_month', $endOfLastMonth)
                      ->setParameter('statut', $statut)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

        // Nombre de client cette année
     public function countComptesThisYear($genre = null)
     {
         $startOfYear = new \DateTime('first day of January this year');
         $startOfYear->setTime(0, 0, 0);
 
         $endOfYear = new \DateTime('first day of January next year');
         $endOfYear->setTime(0, 0, 0);
 
         $qb = $this->createQueryBuilder('c')
                     ->select('COUNT(c.id)')
                     ->where('c.dateCreation >= :start_of_year')
                     ->andWhere('c.dateCreation < :end_of_year')
                     ->andWhere('c.genre = :genre')
                     ->andWhere('c.application = :application_id')
                     ->setParameter('start_of_year', $startOfYear)
                     ->setParameter('end_of_year', $endOfYear)
                     ->setParameter('genre', $genre)
                     ->setParameter('application_id', $this->application->getId())
                     ->getQuery()
                     ->getSingleScalarResult();
        return $qb;
     }

      // Nombre de client année dernière
      public function countComptesLastYear($genre = null)
      {
          $startOfLastYear = new \DateTime('first day of January last year');
          $startOfLastYear->setTime(0, 0, 0);
  
          $endOfLastYear = new \DateTime('first day of January this year');
          $endOfLastYear->setTime(0, 0, 0);
  
          $qb = $this->createQueryBuilder('c')
                      ->select('COUNT(c.id)')
                      ->where('c.dateCreation >= :start_of_last_year')
                      ->andWhere('c.dateCreation < :end_of_last_year')
                      ->andWhere('c.genre = :genre')
                      ->andWhere('c.application = :application_id')
                      ->setParameter('start_of_last_year', $startOfLastYear)
                      ->setParameter('end_of_last_year', $endOfLastYear)
                      ->setParameter('genre', $genre)
                      ->setParameter('application_id', $this->application->getId())
                      ->getQuery()
                      ->getSingleScalarResult();
         return $qb;
      }

      //chart

      public function countClientsThisWeekByDay(\DateTime $day, $genre)
    {
        $startOfDay = clone $day;
        $startOfDay->setTime(0, 0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        return $this->createQueryBuilder('c')
                    ->select('COUNT(c.id)')
                    ->where('c.dateCreation >= :start_of_day')
                    ->andWhere('c.dateCreation < :end_of_day')
                    ->andWhere('c.genre = :genre')
                    ->andWhere('c.application = :application_id')
                    ->setParameter('start_of_day', $startOfDay)
                    ->setParameter('end_of_day', $endOfDay)
                    ->setParameter('genre', $genre)
                      ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    public function countClientsLastWeekByDay($dayOfWeek, $genre)
    {
        $currentDate = new \DateTime();
        $startOfLastWeek = new \DateTime();
        $startOfLastWeek->setISODate((int)$currentDate->format('o'), (int)$currentDate->format('W') - 1);
        $startOfLastWeek->setTime(0, 0, 0);

        $specificDayOfLastWeek = clone $startOfLastWeek;
        $specificDayOfLastWeek->modify('+' . ($dayOfWeek - 1) . ' days');

        $startOfDay = clone $specificDayOfLastWeek;
        $startOfDay->setTime(0, 0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        return $this->createQueryBuilder('c')
                    ->select('COUNT(c.id)')
                    ->where('c.dateCreation >= :start_of_day')
                    ->andWhere('c.dateCreation < :end_of_day')
                    ->andWhere('c.genre = :genre')
                    ->andWhere('c.application = :application_id')
                    ->setParameter('start_of_day', $startOfDay)
                    ->setParameter('end_of_day', $endOfDay)
                    ->setParameter('genre', $genre)
                    ->setParameter('application_id', $this->application->getId())
                    ->getQuery()
                    ->getSingleScalarResult();
    }

    // Nombre de clients inscrits par mois cette année
    public function countClientsRegisteredThisYearByMonth($year, $month, $genre)
    {
        $startDate = new \DateTime("$year-$month-01 00:00:00");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCreation BETWEEN :start_date AND :end_date')
            ->andWhere('c.genre = :genre')
            ->andWhere('c.application = :application_id')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->setParameter('genre', $genre)
            ->setParameter('application_id', $this->application->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Nombre de clients inscrits par mois l'année dernière
    public function countClientsRegisteredLastYearByMonth($year, $month, $genre)
    {
        $startDate = new \DateTime("$year-$month-01 00:00:00");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);

        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateCreation BETWEEN :start_date AND :end_date')
            ->andWhere('c.genre = :genre')
            ->andWhere('c.application = :application_id')
            ->setParameter('start_date', $startDate)
            ->setParameter('end_date', $endDate)
            ->setParameter('genre', $genre)
            ->setParameter('application_id', $this->application->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }
}
