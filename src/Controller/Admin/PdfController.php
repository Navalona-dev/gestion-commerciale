<?php
namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\TCPDFService;

class PdfController extends AbstractController
{
    private $tcpdf;

    public function __construct(TCPDFService $tcpdf)
    {
        $this->tcpdf = $tcpdf;
    }

    #[Route('/admin/pdf', name: 'app_admin_pdf')]
    public function generatePdf(): Response
    {
        // Utiliser l'instance injectée de TCPDFService
        $pdf = $this->tcpdf;

        // Définir les informations du document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Lynah');
        $pdf->SetTitle('Test PDF');
        $pdf->SetSubject('Hello, je teste seulement le PDF en utilisant ce bundle TCPDF');
        $pdf->SetKeywords('PDF');

        // Ajouter une page
        $pdf->AddPage();

        // Définir le contenu du PDF
        $html = '<h1>Bienvenue sur TCPDF</h1><p>Ce PDF a été généré avec TCPDF et Symfony.</p>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Sortie du PDF sous forme de réponse HTTP
        return new Response($pdf->Output('test.pdf', 'I'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
