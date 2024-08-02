<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/historique', name: 'logs')]
class HistoriqueController extends AbstractController
{
    #[Route('/produit', name: '_produit')]
    public function produit(): Response
    {
        $data = [];
        try {
            // Chemin vers le fichier de log
            $logFilePath = $this->getParameter('kernel.project_dir') . '/public/historique/product.txt';

            // Lire le contenu du fichier
            $logContent = file_get_contents($logFilePath);

            // Vérifier si la lecture du fichier a réussi
            if ($logContent === false) {
                throw new \Exception('Impossible de lire le fichier de log.');
            }

            // Diviser le contenu en lignes
            $logLines = explode("\n", $logContent);

            // Filtrer les lignes vides
            $logLines = array_filter($logLines);

            // Trier les lignes par date (du plus récent au plus ancien)
            usort($logLines, function ($a, $b) {
                $dateA = $this->parseLogDate($a);
                $dateB = $this->parseLogDate($b);
                return $dateB <=> $dateA;
            });

            // Passer les lignes de log au template Twig
            $htmlContent = $this->renderView('admin/historique/produit.html.twig', [
                'logLines' => $logLines,
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
