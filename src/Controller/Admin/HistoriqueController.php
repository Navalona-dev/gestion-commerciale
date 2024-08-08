<?php

namespace App\Controller\Admin;

use App\Service\ApplicationManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/historique', name: 'logs')]
class HistoriqueController extends AbstractController
{
    private $application;


    public function __construct(
        ApplicationManager $applicationManager
        )
    {
        $this->application = $applicationManager->getApplicationActive();
    }

    #[Route('/produit', name: '_produit')]
    public function produit(): Response
    {
        $data = [];
        try {
            $logFilePath = $this->getParameter('kernel.project_dir') . '/public/historique/product.txt';
    
            if (!file_exists($logFilePath)) {
                $htmlContent = $this->renderView('admin/historique/produit.html.twig', [
                    'logEmpty' => true
                ]);
    
                $data["html"] = $htmlContent;
                return new JsonResponse($data);
            }
    
            $logContent = file_get_contents($logFilePath);
    
            if ($logContent === false) {
                throw new \Exception('Impossible de lire le fichier de log.');
            }
    
            $logLines = explode("\n", $logContent);
            $logLines = array_filter($logLines);
    
            // Extraire les informations de chaque ligne
            $parsedLines = array_map(function ($line) {
                // Extraire la date et le message JSON de la ligne
                if (preg_match('/^\[([^\]]+)\] [^\:]+: (.+?) ({.*})/', $line, $matches)) {
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
            }, $logLines);
    
            // Filtrer les lignes qui ont échoué à être décodées
            $parsedLines = array_filter($parsedLines);
    
            $idApplication = $this->application->getId();
            $filteredLines = array_filter($parsedLines, function ($line) use ($idApplication) {
                return isset($line['ID Application']) && $line['ID Application'] == $idApplication;
            });

            //dd($filteredLines);
           
            usort($filteredLines, function ($a, $b) {
                $dateA = $this->parseLogDate($a['Date']);
                $dateB = $this->parseLogDate($b['Date']);
                return $dateB <=> $dateA;
            });
    
            $htmlContent = $this->renderView('admin/historique/produit.html.twig', [
                'logLines' => $filteredLines,
                'logEmpty' => false
            ]);
    
            $data["html"] = $htmlContent;
            return new JsonResponse($data);
        } catch (\Exception $exception) {
            $data["exception"] = $exception->getMessage();
            return new JsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    #[Route('/affaire', name: '_affaire')]
    public function affaire(): Response
    {
        $data = [];
        try {
            $logFilePath = $this->getParameter('kernel.project_dir') . '/public/historique/affaire.txt';
    
            if (!file_exists($logFilePath)) {
                $htmlContent = $this->renderView('admin/historique/affaire.html.twig', [
                    'logEmpty' => true
                ]);
    
                $data["html"] = $htmlContent;
                return new JsonResponse($data);
            }
    
            $logContent = file_get_contents($logFilePath);
    
            if ($logContent === false) {
                throw new \Exception('Impossible de lire le fichier de log.');
            }
    
            $logLines = explode("\n", $logContent);
            $logLines = array_filter($logLines);
    
            // Extraire les informations de chaque ligne
            $parsedLines = array_map(function ($line) {
                // Extraire la date et le message JSON de la ligne
                if (preg_match('/^\[([^\]]+)\] [^\:]+: (.+?) ({.*})/', $line, $matches)) {
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
            }, $logLines);
    
            // Filtrer les lignes qui ont échoué à être décodées
            $parsedLines = array_filter($parsedLines);
    
            $idApplication = $this->application->getId();
            $filteredLines = array_filter($parsedLines, function ($line) use ($idApplication) {
                return isset($line['ID Application']) && $line['ID Application'] == $idApplication;
            });

            //dd($filteredLines);
    
            usort($filteredLines, function ($a, $b) {
                $dateA = $this->parseLogDate($a['Date']);
                $dateB = $this->parseLogDate($b['Date']);
                return $dateB <=> $dateA;
            });
    
            $htmlContent = $this->renderView('admin/historique/affaire.html.twig', [
                'logLines' => $filteredLines,
                'logEmpty' => false
            ]);
    
            $data["html"] = $htmlContent;
            return new JsonResponse($data);
        } catch (\Exception $exception) {
            $data["exception"] = $exception->getMessage();
            return new JsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function parseLogDate(string $logLine): \DateTime
    {
        // Extraire la date du log (supposons que la date est au début de la ligne, format [YYYY-MM-DDTHH:MM:SS])
        preg_match('/^\[([^\]]+)\]/', $logLine, $matches);
        return new \DateTime($matches[1] ?? 'now');
    }
}
