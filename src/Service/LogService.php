<?php
namespace App\Service;

class LogService
{
    public function addLog($dossier = null, $infoNiveauSourceInfo = null, $infoNiveauDestinataireInfo = null, $action = null, $envoie = null)
    {
        $logDossier = fopen("./uploads/historiques/log_".$dossier['referencedemande']."_".date("d-m-Y").".txt", "a+");
        fwrite($logDossier, date("d-m-Y H:i:s")." ".$dossier['referencedemande'].": ".strtoupper($action)." efféctué par ".$envoie->getEnvoyerPar()."(".$infoNiveauSourceInfo['libelle'] ."-". $envoie->getService().") vers ".$infoNiveauDestinataireInfo['libelle']."(".$infoNiveauDestinataireInfo['entite'].")"."\n==============================================\n");
        fclose($logDossier);

        return $envoie;

    }

}
