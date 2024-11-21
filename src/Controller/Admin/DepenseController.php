<?php

namespace App\Controller\Admin;

use App\Entity\Depense;
use App\Form\DepenseType;
use App\Service\DepenseService;
use App\Service\ApplicationManager;
use App\Repository\DepenseRepository;
use App\Repository\FactureRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/depense', name: 'depenses')]
class DepenseController extends AbstractController
{
    private $depenseService;
    private $depenseRepository;
    private $application;

    public function __construct(
        DepenseService $depenseService,
        DepenseRepository $depenseRepository,
        ApplicationManager $applicationManager, 

    )
    {
        $this->depenseService = $depenseService;
        $this->depenseRepository = $depenseRepository;
        $this->application = $applicationManager->getApplicationActive();

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
        $existeCompta = $request->getSession()->get('existeCompta');

        $depense = new Depense();

        $form = $this->createForm(DepenseType::class, $depense);
        $data = [];
        try {

            $form->handleRequest($request);

            $beneficeId = $request->getSession()->get('beneficeId');

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->depenseService->add($depense, $existeCompta);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/depense/new.html.twig', [
                'form' => $form->createView(),
                'beneficeId' => $beneficeId, 
                'comptabilite' => $existeCompta
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

    #[Route('/facture', name: '_facture')]
    public function facture(Request $request): Response
    {
        $depenses = null;
        $date = $request->getSession()->get('date');
        if($date) {
            $depenses = $this->depenseRepository->selectDepenseByDate($date);
        } else {
            $depenses = $this->depenseRepository->selectDepenseToday();
        }

        if (count($depenses) > 0) {
            $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/depense/';

            // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
            if (!is_dir($documentFolder)) {
                mkdir($documentFolder, 0777, true); // 0777 pour les permissions, et `true` pour créer récursivement les sous-dossiers
            }
            
            list($pdfContent, $facture) = $this->depenseService->addFacture($depenses, $documentFolder, $request);
            
            $filename = 'Depense' . '-' . $facture->getNumero() . ".pdf";
            $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/depense/' . $filename;
            
            // Sauvegarder le fichier PDF
            file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
            
            return new JsonResponse([
                'status' => 'success',
                'pdfUrl' => $pdfPath,
            ]);
            
        }
        
        return new JsonResponse([]);
    }

    #[Route('/edit/{depense}', name: '_edit')]
    public function edit(Request $request, Depense $depense)
    {
        $existeCompta = $request->getSession()->get('existeCompta');

        $form = $this->createForm(DepenseType::class, $depense);
        $data = [];
        try {

            $form->handleRequest($request);

            $beneficeId = $request->getSession()->get('beneficeId');

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->depenseService->add($depense);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/depense/update.html.twig', [
                'form' => $form->createView(),
                'depense' => $depense,
                'beneficeId' => $beneficeId,
                'comptabilite' => $existeCompta
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


