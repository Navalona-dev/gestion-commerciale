<?php

namespace App\Controller\Admin;

use App\Repository\FourchetteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/fourchette', name: 'fourchettes')]
class FourchetteController extends AbstractController
{
    private $fourchetteRepo;

    public function __construct(
        FourchetteRepository $fourchetteRepo
    )
    {
        $this->fourchetteRepo = $fourchetteRepo;
    }

    #[Route('/', name: '_liste')]
    public function index(): Response
    {
        $data = [];
        try {

            $fourchettes = $this->fourchetteRepo->findByApplication();

            $data["html"] = $this->renderView('admin/fourchette/index.html.twig', [
                'listes' => $fourchettes,
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
