<?php

namespace App\Controller\Admin;

use App\Entity\Fourchette;
use App\Form\FourchetteType;
use App\Service\FourchetteService;
use App\Service\ApplicationManager;
use App\Repository\FourchetteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/fourchette', name: 'fourchettes')]
class FourchetteController extends AbstractController
{
    private $fourchetteRepo;
    private $application;
    private $fourchetteService;

    public function __construct(
        FourchetteRepository $fourchetteRepo,
        ApplicationManager $applicationManager,
        FourchetteService $fourchetteService

    )
    {
        $this->fourchetteRepo = $fourchetteRepo;
        $this->application = $applicationManager->getApplicationActive();
        $this->fourchetteService = $fourchetteService;

    }

    #[Route('/', name: '_liste')]
    public function index(Request $request): Response
    {
        $data = [];
        try {

            $beneficeId = $request->getSession()->get('beneficeId');

            $fourchettes = $this->fourchetteRepo->findByApplication();

            $data["html"] = $this->renderView('admin/fourchette/index.html.twig', [
                'listes' => $fourchettes,
                'beneficeId' => $beneficeId
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

        $beneficeId = $request->getSession()->get('beneficeId');
        
        $fourchette = new Fourchette();

        $form = $this->createForm(FourchetteType::class, $fourchette);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->fourchetteService->add($fourchette);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }

            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/fourchette/new.html.twig', [
                'form' => $form->createView(),
                'beneficeId' => $beneficeId
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

    #[Route('/edit/{id}', name: '_edit')]
    public function edit(Request $request, Fourchette $fourchette)
    {

        $form = $this->createForm(FourchetteType::class, $fourchette);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->fourchetteService->add($fourchette);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }

            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/fourchette/update.html.twig', [
                'form' => $form->createView(),
                'fourchette' => $fourchette
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

    #[Route('/delete/{id}', name: '_delete')]
    public function delete(Request $request, Fourchette $fourchette)
    {
       
        try {
           
            if ($request->isXmlHttpRequest()) {
                $comptabilites = $fourchette->getComptabilites();
                if(count($comptabilites) > 0) {
                    return new JsonResponse(['status' => 'error'], Response::HTTP_OK);
                } else {
                    $this->fourchetteService->remove($fourchette);
                    $this->fourchetteService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                
            }
                
        }  catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }
}
