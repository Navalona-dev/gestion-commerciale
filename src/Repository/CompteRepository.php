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
            c.telephone LIKE :nom OR c.email LIKE :nom OR DATE_FORMAT(c.dateCreation, '%d/%m/%Y') LIKE :nom")
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
        $nom = null,
        $tags = null,
        $dateDu = null,
        $dateAu = null,
        $typeClient = null,
        $typeContrat = null,
        $typeProjet = null,
        $typePaiement = null,
        $produit = null,
        $collaborateur = null,
        $ville = null,
        $limit = null,
        $pg = 1,
        $order = null,
        $utilisateur = null,
        $parcours = null,
        $filtreCa = null,
        $filtreMarge = null,
        $filtreCaSup = null,
        $filtreMargeSup = null,
        $etapes = null,
        $cp = null,
        $bp = null,
        $etat = null,
        $parcoursEnr = null,
        $isCount = false,
        $tagUniqueMulti = null,
        $tabIdTags = null,
        $tabIdTagsNom = null,
        $nomContact = null,
        $autresComptesApplication = null
    ) {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $joins = $conditions = $sqlLimit = "";
        $parameters = [];
        $parameterType = [];

        if (!$isCount) {
            $select = "SELECT compte.*, GROUP_CONCAT(CONCAT('|',compte_tag.tag_id,'|') ORDER BY compte_tag.tag_id ASC) AS tagIds, GROUP_CONCAT(CONCAT('|',Tag.tag,'|') ORDER BY Tag.tag ASC) AS tagNoms FROM Compte compte";
        } else {
            if ($this->application->getId() == 62) {
                $select = "SELECT COUNT(compte.id) as nbCompte, compte.id as idCompte, compte.email as emailCompte, compte.code_postal as codePostalCompte, compte.nom as nomCompte, compte.telephone as telephoneCompte, GROUP_CONCAT(CONCAT('|',compte_tag.tag_id,'|') ORDER BY compte_tag.tag_id ASC) AS tagIds, GROUP_CONCAT(CONCAT('|',nomContact,'|')) AS nomContacts, GROUP_CONCAT(CONCAT('|',prenomContact,'|')) AS prenomContacts, GROUP_CONCAT(CONCAT('|',telephoneContact,'|')) AS telephoneContacts , GROUP_CONCAT(CONCAT('|',portableContact,'|')) AS portableContacts FROM Compte compte";
            } else {
                $select = "SELECT COUNT(compte.id) as nbCompte, compte.id as idCompte, GROUP_CONCAT(CONCAT('|',compte_tag.tag_id,'|') ORDER BY compte_tag.tag_id ASC) AS tagIds FROM Compte compte";
            }
        }
        $conditions = self::conditionConcatener($conditions, "compte.application_id = :applicationId AND (compte.archive IS NULL OR compte.archive = 0)");


        $parameters['applicationId'] = $this->application->getId();
        $parameterType['applicationId'] = ParameterType::INTEGER;

        $collaborateurDroit = $this->droitService->getListUtilisateurCollaborateurAllowed(1, null);

        if (null != $collaborateur and $collaborateur != "") {
            $joins .= " JOIN compte_utilisateurcollaborateur ON compte_utilisateurcollaborateur.compte_id = compte.id
                        JOIN UtilisateurCollaborateur ON  UtilisateurCollaborateur.id = compte_utilisateurcollaborateur.utilisateurcollaborateur_id ";
            $conditions = self::conditionConcatener($conditions, "UtilisateurCollaborateur.utilisateur_id = :collaborateur");
            $parameters['collaborateur'] = $collaborateur;
            $parameterType['collaborateur'] = ParameterType::INTEGER;
        } elseif ($collaborateurDroit) {
            $joins .= " JOIN compte_utilisateurcollaborateur ON compte_utilisateurcollaborateur.compte_id = compte.id
                       JOIN UtilisateurCollaborateur ON  UtilisateurCollaborateur.id = compte_utilisateurcollaborateur.utilisateurcollaborateur_id ";
            $conditions = self::conditionConcatener($conditions, "UtilisateurCollaborateur.id IN (:collaborateur)");
            $parameters['collaborateur'] = $collaborateurDroit;
            $parameterType['collaborateur'] = Connection::PARAM_INT_ARRAY;
        }

        if (null != $nom) {
            $conditions = self::conditionConcatener($conditions, '(compte.nom like :nom or compte.boite_postale like :nom or compte.ville like :nom or compte.adresse like :nom or
                           compte.telephone like :nom or compte.email like :nom or DATE_FORMAT(compte.dateCreation, "%d/%m/%Y") LIKE :nom)');
            $parameters['nom'] = '%' . trim($nom) . '%';
            $parameterType['nom'] = ParameterType::STRING;
        }

        $joins .= " LEFT JOIN compte_tag ON compte_tag.compte_id = compte.id ";
        $joins .= " LEFT JOIN Tag ON Tag.id = compte_tag.tag_id ";

        if (null != $tags) {
            $conditions = self::conditionConcatener($conditions, "compte_tag.tag_id in (:tags)");
            $parameters['tags'] = $tabIdTags;
            $parameterType['tags'] = Connection::PARAM_INT_ARRAY;
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

        if ($typeClient != "") {
            if ($typeClient != "tout") {
                $conditions = self::conditionConcatener($conditions, "compte.genre = :typeClient");
                $parameters['typeClient'] = $typeClient;
                $parameterType['typeClient'] = ParameterType::INTEGER;
            }
        }


        if (null != $typeContrat || null != $typeProjet || null != $typePaiement || null != $produit || null != $filtreCa || null != $filtreMarge || null != $filtreCaSup || null != $filtreMargeSup || null != $etapes || null != $parcours || null != $parcoursEnr) {
            $joins .= " JOIN Affaire ON affaire.compte_id = compte.id ";

            if (null != $typeContrat) {
                $conditions = self::conditionConcatener($conditions, "Affaire.prestation = :typeContrat");
                $parameters['typeContrat'] = $typeContrat;
                $parameterType['typeContrat'] = ParameterType::STRING;
            }

            if (null != $typeProjet) {
                $conditions = self::conditionConcatener($conditions, "Affaire.projet = :typeProjet");
                $parameters['typeProjet'] = $typeProjet;
                $parameterType['typeProjet'] = ParameterType::STRING;
            }

            if (null != $typePaiement) {
                $conditions = self::conditionConcatener($conditions, "Affaire.paiement = :typePaiement");
                $parameters['typePaiement'] = $typePaiement;
                $parameterType['typePaiement'] = ParameterType::STRING;
            }

            if (null != $produit) {
                $joins .= " JOIN produit_affaire ON produit_affaire.affaire_id = Affaire.id ";
                $joins .= " JOIN Produit ON Produit.id = produit_affaire.affaire_id ";

                $conditions = self::conditionConcatener($conditions, "Produit.produitCatalogue_id  = :produit");
                $parameters['produit'] = $produit;
                $parameterType['produit'] = ParameterType::INTEGER;
            }

            if (null != $parcours) {
                $joins .= " LEFT JOIN Parcours ON Parcours.affaire_id = Affaire.id ";
                $parcoursConditionData = [
                    0 => "demandeContactCandidat",
                    1 => "priseDeContact",
                    11 => "pcQualification",
                    12 => "pcRepondeurPlaquette",
                    13 => "pcR1",
                    14 => "pcR2",
                    15 => "pcClasser",
                    2 => "dossierCandidature",
                    21 => "envoiCandidature",
                    22 => "dcSmsEnvoye",
                    23 => "dcIncomplet",
                    24 => "receptionCandidature",
                    25 => "dcR1SuiteEnvoi",
                    26 => "dcR2SuiteEnvoi",
                    27 => "dcClasser",
                    3 => "restitution",
                    31 => "resValidationProfil",
                    311 => "resIssuePlus",
                    3111 => "resRdvFixe",
                    3112 => "resZommRdv",
                    3113 => "res3Qinterv",
                    3114 => "res3QCand",
                    312 => "resIssueMoins",
                    3121 => "resMailRefusCadidature",
                    4 => "journeeDecouverte",
                    41 => "jdFixee",
                    42 => "jdAttenteConfirmation",
                    43 => "jdZoomCandidat",
                    44 => "jdZoomFranchiseur",
                    5 => "envoiDip",
                    51 => "dipPostJd",
                    52 => "dipCandidat",
                    53 => "dipDocSigne",
                    6 => "entretienIndividuel",
                    61 => "eiFixe",
                    62 => "eiZoom",
                    7 => "etatLocalMarche",
                    71 => "envoiElm",
                    72 => "suiviEnvoiElm",
                    8 => "immersion",
                    81 => "jiFranchiseFixe",
                    82 => "jiFranchiseurFixe",
                    9 => "debrief",
                    91 => "debriefCandidat",
                    92 => "debriefFranchiseur",
                    93 => "debriefReflexion",
                    94 => "debriefDecision",
                    10 => "signatureContrat",
                    101 => "signatureContratReservation",
                    102 => "signatureContratFranchise",
                    103 => "scFormationInitiale",
                    104 => "scAgencePointVente",
                    110 => "facturation",
                    111 => "facturationACompte",
                    1111 => "facPreparation",
                    1112 => "facValide",
                    1113 => "facEnvoiFranchiseur",
                    1114 => "facConfirmationReception",
                    112 => "facFinale",
                    1121 => "facFinalePreparation",
                    1122 => "facFinaleValide",
                    1123 => "facFinaleFranchiseur",
                    1124 => "facFinaleConfirmationReception",
                    120 => "reglement",
                    121 => "reglementReglee",
                    122 => "reglementMailFranchiseur",
                    123 => "reglementTelFranchiseur",
                    130 => "stop",
                    131 => "abandonCandidat",
                    132 => "choixAutreReseau",
                    133 => "pasDemandeActivite",
                    134 => "nonSuiteEchangeTel",
                    135 => "pasProjetCreation",
                    136 => "pasCapitalDepart",
                    137 => "pasPlusNouvelle",
                    138 => "refusCabinet",
                    139 => "refusFranchiseur",
                    13101 => "nonAbouti",
                    1311 => "concurrence",
                    1312 => "secteurPasDispo",
                ];

                foreach ($parcoursConditionData as $key => $field) {
                    if (in_array($key, $parcours)) {
                        $conditions = self::conditionConcatener($conditions, sprintf('Parcours.%s IS NOT NULL', $field));
                    }
                }
            }

            if (null != $parcoursEnr) {
                $joins .= " LEFT JOIN ParcoursEnr on ParcoursEnr.affaire_id = Affaire.id ";
                $parcoursEnrConditionData = [
                    '01' => 'ahValidee',
                    '02' => 'appel1',
                    '03' => 'appel2',
                    '04' => 'cofrac',
                    '05' => 'devisOffreSigne',
                    '06' => 'ahEnvoyee',
                    '07' => 'devisOffreEnvoye',
                    '08' => 'installationPlanifiee',
                    '09' => 'installationTerminee',
                    '10' => 'ko',
                    '11' => 'visiteTechniquePlanifiee',
                    '12' => 'vtOk',
                ];

                foreach ($parcoursEnrConditionData as $key => $field) {
                    if (in_array($key, $parcoursEnr)) {
                        $conditions = self::conditionConcatener($conditions, sprintf('ParcoursEnr.%s IS NOT NULL', $field));
                    }
                }
            }

            if (null != $etapes) {

                $joins .= " LEFT JOIN Etapes ON Etapes.affaire_id = Affaire.id ";
                $etapeConditionData = [
                    '12' => 'receptionCandidature',
                    '13' => 'presentationEnvoyee',
                    '14' => 'appelValidation',
                    '15' => 'nonRetour',
                    '16' => 'dossierCandidatureRecu',
                    '17' => 'accordConfidentialiteSigne',
                    '18' => 'entretienTelephonique',
                    '19' => 'testPersonnalite',
                    '20' => 'rdvReunion',
                    '21' => 'visitePilote',
                    '22' => 'validationClient',
                    '23' => 'dipSigne',
                    '24' => 'previsionnelRealise',
                    '25' => 'rzSignee',
                    '26' => 'bailSigne',
                    '27' => 'contratAffiliationSigne',
                    '28' => 'arretCandidature',
                ];

                foreach ($etapeConditionData as $key => $field) {
                    if (in_array($key, $etapes)) {
                        $conditions = self::conditionConcatener($conditions, sprintf('Etapes.%s IS NOT NULL', $field));
                    }
                }
            }
        }

        if (null != $ville) {
            $conditions = self::conditionConcatener($conditions, "(compte.adresse LIKE :ville or compte.code_postal like :ville or compte.ville like :ville)");
            $parameters['ville'] = '%' . $ville . '%';
            $parameterType['ville'] = ParameterType::STRING;
        }
        if (null != $limit) {
            $sqlLimit = ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($pg);
            //$parameters['sqlLimit'] =  intval($limit);
            //$parameters['pg'] =  intval($pg);
        }
        if (null != $utilisateur) {
            $joins .= " JOIN compte_utilisateur ON compte_utilisateur.compte_id = compte.id ";
            $conditions = self::conditionConcatener($conditions, "compte_utilisateur.utilisateur = :utilisateur");
            $parameters['utilisateur'] = $utilisateur->getId();
            $parameterType['utilisateur'] = ParameterType::INTEGER;
        }

        if (null != $nomContact) {
            $joins .= " JOIN (select utilisateur_id,compte_id from compte_utilisateur) cptU  ON cptU.compte_id = compte.id JOIN ( select id, nom, prenom from Utilisateur where nom LIKE '%" . trim($nomContact) . "%' or prenom LIKE '%" . trim($nomContact) . "%') ut ON cptU.utilisateur_id = ut.id";
        }
        if ($this->application->getId() == 62) {
            $joins .= " LEFT JOIN (select utilisateur_id,compte_id, nomContact, prenomContact, telephoneContact, portableContact  from compte_utilisateur cptU JOIN ( select id, nom as nomContact, prenom as prenomContact,telephone as telephoneContact, portable as portableContact from Utilisateur) ut ON cptU.utilisateur_id = ut.id ) usr ON usr.compte_id = compte.id";
        }


        if (null != $filtreCaSup && null == $filtreCa) {
            $conditions = self::conditionConcatener($conditions, "Affaire.ca > :ca");
            $parameters['ca'] = $filtreCaSup;
            $parameterType['ca'] = ParameterType::STRING;
        }
        if (null != $filtreCa && null == $filtreCaSup) {
            $conditions = self::conditionConcatener($conditions, "Affaire.ca <= :ca");
            $parameters['ca'] = $filtreCa;
            $parameterType['ca'] = ParameterType::STRING;
        }
        if (null != $filtreCa && null != $filtreCaSup) {
            $conditions = self::conditionConcatener($conditions, "Affaire.ca > :caSup AND Affaire.ca <= :caInf");
            $parameters['caSup'] = $filtreCaSup;
            $parameterType['caSup'] = ParameterType::STRING;
            $parameters['caInf'] = $filtreCa;
            $parameterType['caInf'] = ParameterType::STRING;
        }
        if (null != $filtreMargeSup && null == $filtreMarge) {
            $conditions = self::conditionConcatener($conditions, "((Affaire.ca - Affaire.cout)*100)/Affaire.ca > :marge");
            $parameters['marge'] = $filtreMargeSup;
            $parameterType['marge'] = ParameterType::STRING;
        }
        if (null != $filtreMarge && null == $filtreMargeSup) {
            $conditions = self::conditionConcatener($conditions, "((Affaire.ca - Affaire.cout)*100)/Affaire.ca < :marge");
            $parameters['marge'] = $filtreMarge;
            $parameterType['marge'] = ParameterType::STRING;
        }
        if (null != $filtreMarge && null != $filtreMargeSup) {
            $conditions = self::conditionConcatener($conditions, "((Affaire.ca - Affaire.cout)*100)/Affaire.ca > :margeSup AND ((Affaire.ca - Affaire.cout)*100)/Affaire.ca <= :margeInf");
            $parameters['margeSup'] = $filtreMargeSup;
            $parameterType['margeSup'] = ParameterType::STRING;
            $parameters['margeInf'] = $filtreMarge;
            $parameterType['margeInf'] = ParameterType::STRING;
        }

        if (null != $cp) {
            if (@preg_match("/;/", $cp)) {
                $tabCp = explode(";", $cp);
                $cp = $tabCp;
            }
            if (is_array($cp)) {
                $cpCondition = "";

                foreach ($cp as $k => $unCp) {
                    if ($k == 0) $cpCondition = "(";
                    $cpCondition .= " compte.code_postal LIKE :unCp" . $k;

                    if ($k < count($cp) - 1) {
                        $cpCondition .= " OR ";
                    } else {
                        $cpCondition .= " )";
                    }

                    $parameters['unCp' . $k] = "" . $unCp . "%";
                    $parameterType['unCp' . $k] = ParameterType::STRING;
                }


                $conditions = self::conditionConcatener($conditions, $cpCondition);
            } else {
                $conditions = self::conditionConcatener($conditions, "compte.code_postal LIKE :cp");
                $parameters["cp"] = "" . $cp . "%";
                $parameterType["cp"] = ParameterType::STRING;
            }
        }

        if (null != $bp) {
            $conditions = self::conditionConcatener($conditions, "compte.boite_postale LIKE :bp");
            $parameters['bp'] = "%" . $bp . "%";
            $parameterType["bp"] = ParameterType::STRING;
        }

        //filtre par etat
        if (null != $etat) {

            if ($etat == "pre") {
                $conditions = self::conditionConcatener($conditions, "(compte.etat LIKE :etat OR compte.etat IS NULL)");
            } else {
                $conditions = self::conditionConcatener($conditions, "compte.etat LIKE :etat");
            }

            $parameters['etat'] = "%" . $etat . "%";
            $parameterType["etat"] = ParameterType::STRING;
        }

        /**
         * Get all compte partenaire
         */
        if (!is_null(($autresComptesApplication)) && !empty($autresComptesApplication)) {
            $conditions .= " OR compte.id IN (" . $autresComptesApplication . ")";
        }
        /**
         * End get all compte partenaire
         */

        $conditions .= ' group by compte.id ';

        if ($tagUniqueMulti == "unique" and count($tabIdTags) > 0) {

            $havingCond = ' HAVING (';

            if ($this->application->getId() == 54) {
                foreach ($tabIdTagsNom as $i => $tagNom) {
                    $havingCond .= ' tagNoms like :tagNom' . $i;
                    $parameters['tagNom' . $i] = '%|' . $tagNom . '|%';
                    $parameterType["tagNom" . $i] = ParameterType::STRING;

                    if ($i < count($tabIdTagsNom) - 1) {
                        $havingCond .= " AND ";
                    } else {
                        $havingCond .= " )";
                    }
                }

                $conditions .= ' ' . $havingCond;
            } else {
                if (count($tabIdTags) == 1) {
                    $tabIdTagsFormatted = '|' . $tabIdTags[0] . '|';
                } else {
                    $tabIdTagsFormatted = '|' . implode('|,|', $tabIdTags) . '|';
                }
                $conditions .= ' HAVING (tagIds = :tagIds)';
                $parameters['tagIds'] = $tabIdTagsFormatted;
                $parameterType["tagIds"] = ParameterType::STRING;
            }
        }

        if ($this->application->getId() == 8) {
            $tabOrder = [
                0 => 'compte.dateCreation',
                1 => 'compte.nom',
                2 => 'compte.email',
                3 => 'compte.telephone',
                4 => 'compte.boite_postale',
                5 => 'compte.ville'
            ];
        } elseif ($this->application->getId() == 27) {
            $tabOrder = [
                0 => 'compte.dateCreation',
                1 => 'compte.nom',
                2 => 'compte.email',
                3 => 'compte.telephone',
                4 => 'compte.pays',
                5 => 'compte.ca',
                6 => 'compte.marge',
                7 => 'compte.nbAffaire'
            ];
        } else {
            $tabOrder = [
                0 => 'compte.dateCreation',
                1 => 'compte.nom',
                2 => 'compte.email',
                3 => 'compte.telephone',
                4 => 'compte.adresse',

            ];
        }


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
        //dd($rawSql, $parameters, $parameterType);
        $connection = $this->getEntityManager()->getConnection();
        try {

            if ($isCount) {
                if ($this->application->getId() == 62) {
                    $listId = $connection->fetchAllAssociative($rawSql, $parameters, $parameterType);
                    $tab = [];
                    $total = count($listId);
                    $tab['total'] = $total;
                    $tab['listIdCompte'] = $listId;
                    return $tab;
                } else {
                    return $connection->fetchOne('SELECT COUNT(*) AS nbCompte FROM (' . $rawSql . ') AS ROWS_LINE ', $parameters, $parameterType);
                }
            }

            return $connection->fetchAllAssociative($rawSql, $parameters, $parameterType);
        } catch (\Exception $exception) {
        }
        if ($isCount) {
            return 0;
        }

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
}
