<?php

namespace App\Controller\Admin;

use App\Service\AccesService;
use App\Service\ApplicationManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/import/produit', name: 'imports')]
class ImportProduitController extends AbstractController
{
    private $accesService;
    private $application;

    public function __construct(ApplicationManager $applicationManager, AccesService $accesService)
    {
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
    }

    #[Route('/', name: '_liste')]
    public function index(): Response
    {
        $data = [];
        try {

           
            $data["html"] = $this->renderView('admin/import_produit/index.html.twig', [
                
            ]);
           
            return new JsonResponse($data);

        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }
}
