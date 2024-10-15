<?php

namespace App\Controller\Admin;

use App\Entity\Benefice;
use App\Form\BeneficeType;
use App\Service\BeneficeService;
use App\Service\ApplicationManager;
use App\Repository\DepenseRepository;
use App\Repository\FactureRepository;
use App\Repository\BeneficeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/benefice', name: 'benefices')]
class BeneficeController extends AbstractController
{
    private $factureRepository;
    private $beneficeService;
    private $depenseRepository;
    private $beneficeRepo;
    private $application;

    public function __construct(
        FactureRepository $factureRepository,
        BeneficeService $beneficeService,
        DepenseRepository $depenseRepository,
        BeneficeRepository $beneficeRepo,
        ApplicationManager $applicationManager


    )
    {
        $this->factureRepository = $factureRepository;
        $this->beneficeService = $beneficeService;
        $this->depenseRepository = $depenseRepository;
        $this->beneficeRepo = $beneficeRepo;
        $this->application = $applicationManager->getApplicationActive();
        
    }

    #[Route('/', name: '_liste')]
    public function index(): Response
    {
        $data = [];
        try {

            $benefices = $this->beneficeRepo->findByApplication();

            $data["html"] = $this->renderView('admin/benefice/index.html.twig', [
                'listes' => $benefices,
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
        $benefice = new Benefice();

        $form = $this->createForm(BeneficeType::class, $benefice);
        $data = [];
        try {
            $facturesToday = $this->factureRepository->selectFactureToday('regle');
            
            $espece = 0;
            $mvola = 0;
            $airtel = 0;
            $orange = 0;

            foreach($facturesToday as $factureToday) {
                $espece += $factureToday['espece'];
                $mvola += $factureToday['mVola'];
                $orange += $factureToday['orangeMoney'];
                $airtel += $factureToday['airtelMoney'];
            }

            $total = $espece + $mvola + $orange + $airtel;
            $mobileMoney = $mvola + $orange + $airtel;

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {

                    if (count($facturesToday) > 0) {
                        $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/benefice/';
            
                        // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
                        if (!is_dir($documentFolder)) {
                            mkdir($documentFolder, 0777, true);
                        }
                        
                        list($pdfContent, $facture, $returnBenefice) = $this->beneficeService->add($benefice, $documentFolder, $request, $espece, $total, $mobileMoney, $facturesToday);

                        $filename = 'Benefice' . '-' . $facture->getNumero() . ".pdf";
                        $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/benefice/' . $filename;
                        
                        // Sauvegarder le fichier PDF
                        file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                        
                        return new JsonResponse([
                            'status' => 'success',
                            'pdfUrl' => $pdfPath,
                            'id' => $returnBenefice->getId()
                        ]);
                        
                    }
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/benefice/new.html.twig', [
                'form' => $form->createView(),
                'espece' => $espece,
                'orange' => $orange,
                'mvola' => $mvola,
                'airtel' => $airtel,
                'facturesToday' => $facturesToday,
                'benefice' => $benefice,
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

    #[Route('/{id}', name: '_liste_one')]
    public function indexOne(Request $request, Benefice $benefice): Response
    {
        $data = [];
        try {
            
            //$request->getSession()->set('beneficeId', $benefice->getId());

            $depensesToday = $this->depenseRepository->selectDepenseToday();

            $request->getSession()->set('depenses', $depensesToday);
            $request->getSession()->set('benefice', $benefice);

            $data["html"] = $this->renderView('admin/benefice/detail.html.twig', [
                'depensesToday' => $depensesToday,
                'benefice' => $benefice,
                'application' => $this->application
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
