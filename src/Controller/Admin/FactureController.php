<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/facture', name: 'factures')]
class FactureController extends AbstractController
{
    #[Route('/', name: '_liste')]
    public function index(): Response
    {

        $data = [];
        try {
            
            /*$categories = $categorieService->getAllCategories();
            if ($categories == false) {
                $categories = [];
            }*/
           
            $data["html"] = $this->renderView('admin/facture/index.html.twig', [
                //'listes' => $categories,
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }
}
