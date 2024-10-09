<?php
/**
 * User: Navalona
 * Date: 4/08/24
 * Time: 21:23
 */

namespace App\Service;


use Symfony\Component\HttpFoundation\Request;

class LogService
{
    public function addLog(Request $request, $action = null, $nameAppli = null, $fileId = null, $data = [])
    {
        if (null != $nameAppli) {
            if (null != $fileId) {
                $log = fopen("./uploads/APP_".$nameAppli."/historique/log_".$nameAppli."_".$fileId."_".date("Y-m-d").".txt", "a+");
            } else {
                $log = fopen("./uploads/APP_".$nameAppli."/historique/log_".$nameAppli."_".date("Y-m-d").".txt", "a+");
            }
            
        }else {
            $log = fopen("./uploads/historique/log_".date("Y-m-d").".txt", "a+");
        }
        
        fwrite($log, date("Y-m-d H:i:s")." :".$action." ".json_encode($data)."\n==============================================\n");
        fclose($log);

        return $request;

    }

    function getFilesStartingWith($directory, $prefix)
    {
        // Assurez-vous que le répertoire se termine par un slash
        $directory = rtrim($directory, '/') . '/';

        // Vérifiez si le répertoire existe
        if (!is_dir($directory)) {
            throw new \Exception("Le répertoire spécifié n'existe pas : $directory");
        }

        // Ouvrir le répertoire
        $files = scandir($directory);

        // Filtrer les fichiers qui commencent par le préfixe
        $filteredFiles = array_filter($files, function($file) use ($prefix) {
            return strpos($file, $prefix) === 0 && !is_dir($file);
        });

        // Retourner les fichiers filtrés
        return $filteredFiles;
    }

    public function getContentLog($logFilePath, $prefix)
    {
        // Affichez les fichiers trouvés
        $files = $this->getFilesStartingWith($logFilePath, $prefix);
        $tabContents = [];
        foreach ($files as $file) {
            $logFile = $logFilePath.$file;
            $logContent = file_get_contents($logFile);
            
            if ($logContent === false) {
                throw new \Exception('Impossible de lire le fichier de log.');
            }

            $logLines = explode("\n==============================================\n", $logContent);
            
            $logLines = array_filter($logLines);
            if (count($logLines) > 0) {
                foreach ($logLines as $key => $logLine) {
                    array_push($tabContents, $logLine);
                }
            }
        
        }

        // Extraire les informations de chaque ligne
        $parsedLines = array_map(function ($line) {
            // Extraire la date et le message JSON de la ligne

        $pattern = '/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) :(\w+) (.+)$/';
            if (preg_match($pattern, $line, $matches)) {
                $date = $matches[1];
                $message = $matches[2];
                $jsonString = $matches[3];

                // Décoder le JSON
                $decoded = json_decode($jsonString, true);

                // Ajouter la date et le message aux données décodées
                if (json_last_error() === JSON_ERROR_NONE) {
                    $decoded['Date'] = $date;
                    $decoded['Message'] = $message;
                    return $decoded;
                } else {
                    dump(json_last_error_msg()); // Pour débogage
                }
            }

            return null;
        }, $tabContents);
       
        // Filtrer les lignes qui ont échoué à être décodées
        $parsedLines = array_filter($parsedLines);
        return $parsedLines;
    }

}
