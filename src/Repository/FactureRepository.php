<?php

namespace App\Repository;

use App\Entity\Facture;
use App\Service\ApplicationManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @extends ServiceEntityRepository<Facture>
 */
class FactureRepository extends ServiceEntityRepository
{
    private $connection;
    public function __construct(ManagerRegistry $registry, ApplicationManager $applicationManager, Connection $connection)
    {
        parent::__construct($registry, Facture::class);
        $this->application = $applicationManager->getApplicationActive();
        $this->connection = $connection;
    }

    public function getLastValideFacture($type = "Facture")
    {
        $query = $this->createQueryBuilder('f')
            ->join('f.compte', 'c')
            
            ->andWhere('f.type = :type AND (f.isValid = 1 OR f.numero is not null)')
            ->setParameter('type', $type)
            ;
   
        $query = $query
        ->andWhere("f.application = :application")
        ->setParameter('application', $this->application)
        ;
   
        $results = $query->getQuery()->getResult();

        $tabNumero = [];

        if ($results) {
            foreach ($results as $result) {
                $tabNumero [] = intval($result->getNumero());
            }

            arsort($tabNumero);

            //Pour ordonner la clé
            $tabNumero = array_merge($tabNumero, []);
        }

        return $tabNumero;
    }

    public function getAllFactures()
    {
        $sql = "SELECT f.id, f.type, f.numero, f.prixHt, f.prixTtc, f.solde, f.statut, f.reglement, f.numeroCommande, f.etat,
                f.file, f.dateCreation, f.isValid, f.remise, c.id as compteId, c.nom as compte, a.id as affaireId, a.nom as affaire
                FROM `Facture` f 
                LEFT JOIN `compte` c ON f.compte_id = c.id 
                LEFT JOIN `affaire` a ON f.affaire_id = a.id 
                WHERE f.application_id = ".$this->application->getId()." 
                ORDER BY f.dateCreation DESC";

            $query = $this->connection->prepare($sql);
        
            $query = $this->connection->executeQuery($sql);
    
            return $query->fetchAll(); 
    }

    public function getAllFacturesByAffaire($affaireId = null)
    {
        $sql = "SELECT f.id, f.type, f.numero, f.prixHt, f.prixTtc, f.solde, f.statut, f.reglement, f.numeroCommande, f.etat,
                f.file, f.dateCreation, f.isValid, f.remise, c.nom as compte, a.nom as affaire, fe.id as factureEcheances
                FROM `Facture` f 
                LEFT JOIN `compte` c ON f.compte_id = c.id 
                LEFT JOIN `affaire` a ON f.affaire_id = a.id 
                LEFT JOIN `FactureEcheance` fe ON f.id = fe.facture_id
                WHERE f.application_id = ".$this->application->getId()." AND f.isEcheance = 0 OR f.isEcheance IS NULL 
                ";

                if ($affaireId != null) {
                    $sql .= " and a.id = ".$affaireId."";
                }

            $sql .= " ORDER BY f.dateCreation DESC";


            $query = $this->connection->prepare($sql);
        
            $query = $this->connection->executeQuery($sql);
    
            return $query->fetchAll(); 
    }


    public function searchFactureRawSql(
        $genre = 1,
        $nom = null,
        $dateDu = null,
        $dateAu = null,
        $etat = null,
        $limit = null,
        $pg = 1,
        $order = null,
        $isCount = false,
        $search = null,
        $statutPaiement = null,
        $datePaieDu = null,
        $datePaieAu = null,
        $tabIdFactureFiltered = null
    ) {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
     
        $joins = $conditions = $sqlLimit = "";
        $parameters = [];
        $parameterType = [];
       //dd($nom, $search,$statutPaiement, $dateDu, $dateAu, $datePaieDu, $datePaieAu );
        if (!$isCount) {
            $select = "SELECT f.id, f.type, f.numero, f.prixHt, f.prixTtc, f.solde, f.statut, f.reglement, f.numeroCommande, f.etat,
                f.file, f.dateCreation, f.isValid, f.file as fichier, f.remise, compte.id as compteId, compte.nom as compte, a.nom as nomAffaire,a.paiement as statutPaiement, a.id as affaireId FROM Facture f  LEFT JOIN `compte` compte ON f.compte_id = compte.id LEFT JOIN `affaire` a ON f.affaire_id = a.id";
        } else {
            $select = "SELECT COUNT(f.id) as nbFacture, f.id, f.type, f.numero, f.prixHt, f.prixTtc, f.solde, f.statut, f.reglement, f.numeroCommande, f.etat,
                f.file, f.dateCreation, f.isValid, f.file as fichier, f.remise,compte.id as compteId,  compte.nom as compte, a.nom as nomAffaire,a.paiement as statutPaiement,  a.id as affaireId FROM Facture f  LEFT JOIN `compte` compte ON f.compte_id = compte.id LEFT JOIN `affaire` a ON f.affaire_id = a.id";
        }
        $conditions = self::conditionConcatener($conditions, "f.application_id = :applicationId");


        $parameters['applicationId'] = $this->application->getId();
        $parameterType['applicationId'] = ParameterType::INTEGER;

        if (null != $nom && $nom != "") {
            $conditions = self::conditionConcatener($conditions, '(compte.nom like :nom or compte.adresse like :nom or
                           compte.telephone like :nom or compte.email like :nom)');
            $parameters['nom'] = '%' . trim($nom) . '%';
            $parameterType['nom'] = ParameterType::STRING;
        }
        
        if (null != $statutPaiement && $statutPaiement != "") {
            $conditions = self::conditionConcatener($conditions, "a.paiement = :statutPaiement");
            $parameters['statutPaiement'] = $statutPaiement;
            $parameterType['statutPaiement'] = ParameterType::STRING;
        }

        if (null != $search && $search != "") {
            $search = $search['value'];
            $conditions = self::conditionConcatener($conditions, '(compte.nom like :search or compte.adresse like :search or
                           compte.telephone like :search or compte.email like :search)');
            $parameters['search'] = '%' . trim($search) . '%';
            $parameterType['search'] = ParameterType::STRING;
        }

        if (null != $dateDu && null != $dateAu) {
            $conditions = self::conditionConcatener($conditions, "f.dateCreation >= :dateDu");
            $conditions = self::conditionConcatener($conditions, "f.dateCreation <= :dateAu");

            $parameters['dateDu'] = $dateDu->format("Y-m-d");
            $parameterType['dateDu'] = ParameterType::STRING;
            $parameters['dateAu'] = $dateAu->format("Y-m-d");
            $parameterType['dateAu'] = ParameterType::STRING;
        } elseif (null != $dateDu && null == $dateAu) {
            $conditions = self::conditionConcatener($conditions, "f.dateCreation >= :dateDu");
            $parameters['dateDu'] = $dateDu->format("Y-m-d");
            $parameterType['dateDu'] = ParameterType::STRING;
        } elseif (null == $dateDu && null != $dateAu) {
          
            $conditions = self::conditionConcatener($conditions, "f.dateCreation <= :dateAu");
            $parameters['dateAu'] = $dateAu->format("Y-m-d");
            $parameterType['dateAu'] = ParameterType::STRING;
        }
        
        if (null != $datePaieDu && null != $datePaieAu) {
            $joins .= " LEFT JOIN ReglementFacture rf ON rf.facture_id = f.id ";
            $conditions = self::conditionConcatener($conditions, "rf.dateReglement >= :datePaieDu");
            $conditions = self::conditionConcatener($conditions, "rf.dateReglement <= :datePaieAu");

            $parameters['datePaieDu'] = $datePaieDu->format("Y-m-d");
            $parameterType['datePaieDu'] = ParameterType::STRING;
            $parameters['datePaieAu'] = $datePaieAu->format("Y-m-d");
            $parameterType['datePaieAu'] = ParameterType::STRING;
        } elseif (null != $datePaieDu && null == $datePaieAu) {
            $joins .= " LEFT JOIN ReglementFacture rf ON rf.facture_id = f.id ";
            $conditions = self::conditionConcatener($conditions, "rf.dateReglement >= :datePaieDu");
            $parameters['datePaieDu'] = $datePaieDu->format("Y-m-d");
            $parameterType['datePaieDu'] = ParameterType::STRING;
        } elseif (null == $datePaieDu && null != $datePaieAu) {
            $joins .= " LEFT JOIN ReglementFacture rf ON rf.facture_id = f.id ";
            $conditions = self::conditionConcatener($conditions, "rf.dateReglement <= :datePaieAu");
            $parameters['datePaieAu'] = $datePaieAu->format("Y-m-d");
            $parameterType['datePaieAu'] = ParameterType::STRING;
        }
        
        //if ($genre != "") {
            $conditions = self::conditionConcatener($conditions, "compte.genre = :typeClient");
            $parameters['typeClient'] = $genre;
            $parameterType['typeClient'] = ParameterType::INTEGER;
            
        //}

        if ($tabIdFactureFiltered != null && count($tabIdFactureFiltered)> 0) {
            $conditions .= " and f.id IN (".implode(",",$tabIdFactureFiltered ).")";
        }

        if (null != $limit) {
            $sqlLimit = ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($pg);
            //$parameters['sqlLimit'] =  intval($limit);
            //$parameters['pg'] =  intval($pg);
        }
        
        //filtre par etat
        /*if (null != $etat) {

            $conditions = self::conditionConcatener($conditions, "compte.etat LIKE :etat");

            $parameters['etat'] = "%" . $etat . "%";
            $parameterType["etat"] = ParameterType::STRING;
        }*/


       // $conditions .= ' group by compte.id ';

        
        $tabOrder = [
            0 => 'f.dateCreation',
            1 => 'f.nom',
            2 => 'f.email',
            3 => 'f.telephone',
            4 => 'f.adresse',

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
                $conditions .= ' ORDER BY f.dateCreation DESC';
            }
        } else {
            $conditions .= ' ORDER BY f.dateCreation DESC';
        }

        $rawSql = $select . ' ' . $joins . ' WHERE ' . $conditions . ' ' . ($sqlLimit ?? '');
        
        $connection = $this->getEntityManager()->getConnection();
        try {
            if ($isCount) {
                //dd($rawSql, $connection->fetchAllAssociative($rawSql, $parameters, $parameterType), $connection->fetchOne('SELECT COUNT(*) AS nbFacture FROM (' . $rawSql . ') AS ROWS_LINE ', $parameters, $parameterType), $rawSql, $parameters, $parameterType, $isCount);
                //dd($connection->fetchOne('SELECT nbFacture FROM (' . $rawSql . ') AS ROWS_LINE ', $parameters, $parameterType));
                return $connection->fetchOne('SELECT nbFacture FROM (' . $rawSql . ') AS ROWS_LINE ', $parameters, $parameterType);
                
            }
           // dd($connection->fetchAllAssociative($rawSql, $parameters, $parameterType), $connection->fetchOne('SELECT COUNT(*) AS nbFacture FROM (' . $rawSql . ') AS ROWS_LINE ', $parameters, $parameterType), $rawSql, $parameters, $parameterType, $isCount);
            return $connection->fetchAllAssociative($rawSql, $parameters, $parameterType);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
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
}
