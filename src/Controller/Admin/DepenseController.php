<?php

namespace App\Controller\Admin;

use App\Entity\Depense;
use App\Form\DepenseType;
use App\Service\DepenseService;
use App\Repository\FactureRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/depense', name: 'depenses')]
class DepenseController extends AbstractController
{
    private $depenseService;

    public function __construct(
        DepenseService $depenseService,
    )
    {
        $this->depenseService = $depenseService;
    }

    #[Route('/', name: '_liste')]
    public function index(): Response
    {
        $data = [];
        try {

            $data["html"] = $this->renderView('admin/depense/index.html.twig', [
                //'listes' => $affaires,
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/new', name: '_create')]
    public function create(Request $request)
    {
        $depense = new Depense();

        $form = $this->createForm(DepenseType::class, $depense);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->depenseService->add($depense);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/depense/new.html.twig', [
                'form' => $form->createView(),
            ]);
           
            return new JsonResponse($data);

        }  catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }

        return new JsonResponse($data);
    }

    #[Route('/edit/{depense}', name: '_edit')]
    public function edit(Request $request, Depense $depense)
    {
        $form = $this->createForm(DepenseType::class, $depense);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->depenseService->add($depense);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/depense/update.html.twig', [
                'form' => $form->createView(),
                'depense' => $depense
            ]);
           
            return new JsonResponse($data);

        }  catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }

        return new JsonResponse($data);
    }

    #[Route('/delete/{depense}', name: '_delete')]
    public function delete(Request $request, Depense $depense)
    {
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->depenseService->remove($depense);
                $this->depenseService->update();
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
            }
                
        }  catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
    }
}
