<?php

namespace App\Controller\Admin;

use App\Entity\Facture;
use App\Entity\Comptabilite;
use App\Form\ComptabiliteType;
use App\Entity\MethodePaiement;
use App\Form\MethodePaiementType;
use App\Service\ComptabiliteService;
use App\Repository\DepenseRepository;
use App\Repository\FactureRepository;
use App\Repository\MethodePaiementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/comptabilite', name: 'comptabilites')]
class ComptabiliteController extends AbstractController
{
    private $depenseRepository;
    private $factureRepository;
    private $comptabiliteService;
    private $methodePaiementRepo;

    public function __construct(
        DepenseRepository $depenseRepository,
        FactureRepository $factureRepository,
        ComptabiliteService $comptabiliteService,
        MethodePaiementRepository $methodePaiementRepo
    )
    {
        $this->depenseRepository = $depenseRepository;
        $this->factureRepository = $factureRepository;
        $this->comptabiliteService = $comptabiliteService;
        $this->methodePaiementRepo = $methodePaiementRepo;
    }

    #[Route('/', name: '_liste')]
    public function index(): Response
    {
        $data = [];
        try {

            $depensesToday = $this->depenseRepository->selectDepenseToday();
            $facturesToday = $this->factureRepository->selectFactureToday('regle');
            $methodePaiementsToday = $this->methodePaiementRepo->selectMethodeToday();

            $data["html"] = $this->renderView('admin/comptabilite/index.html.twig', [
                //'listes' => $affaires,
                'depensesToday' => $depensesToday,
                'facturesToday' => $facturesToday,
                'methodePaiementsToday' => $methodePaiementsToday
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
        $comptabilite = new Comptabilite();

        $form = $this->createForm(ComptabiliteType::class, $comptabilite);
        $data = [];
        try {

            $form->handleRequest($request);

            $depenses = $request->getSession()->get('depenses');
            $benefice = $request->getSession()->get('benefice');

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {

                    if (count($facturesToday) > 0) {
                        $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/comptabilite/';
            
                        // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
                        if (!is_dir($documentFolder)) {
                            mkdir($documentFolder, 0777, true);
                        }
                        
                        list($pdfContent, $facture, $returnComptabilite) = $this->comptabiliteService->addComptabilite($comptabilite, $documentFolder, $request, $depenses, $benefice);

                        $filename = 'Comptabilite' . '-' . $facture->getNumero() . ".pdf";
                        $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/comptabilite/' . $filename;
                        
                        // Sauvegarder le fichier PDF
                        file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                        
                        return new JsonResponse([
                            'status' => 'success',
                            'pdfUrl' => $pdfPath,
                        ]);
                        
                    }
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/comptabilite/new.html.twig', [
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

    #[Route('/detail/paiement/{facture}', name: '_detail_paiement')]
    public function detailPaiement(Facture $facture): Response
    {
        $data = [];
        try {

            $methodePaiements = $facture->getMethodePaiements();

            $data["html"] = $this->renderView('admin/comptabilite/detail_methode_paiement.html.twig', [
                'methodePaiements' => $methodePaiements,
                'facture' => $facture,
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/nouveau/methode/paiement/{factureId}', name: '_new_methode_paiement')]
    public function nouveauMethodePaiement(Request $request, $factureId)
    {
        $facture = $this->factureRepository->findOneBy(['id' => $factureId]);

        $methodePaiement = new MethodePaiement();

        $form = $this->createForm(MethodePaiementType::class, $methodePaiement);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->comptabiliteService->add($methodePaiement, $facture);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/comptabilite/new_methode_paiement.html.twig', [
                'form' => $form->createView(),
                'facture' => $facture
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

    #[Route('/methode/paiement/edit/{methode}', name: '_edit_methode_paiement')]
    public function editMethodePaiement(Request $request, $methode)
    {
        $methodePaiement = $this->methodePaiementRepo->findOneBy(['id' => $methode]);

        $facture = $methodePaiement->getFacture();

        $form = $this->createForm(MethodePaiementType::class, $methodePaiement);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $this->comptabiliteService->add($methodePaiement, $facture);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/comptabilite/update_methode_paiement.html.twig', [
                'form' => $form->createView(),
                'facture' => $facture,
                'methodePaiement' => $methodePaiement
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

    #[Route('/methode/paiement/delete/{methode}', name: '_delete_methode_paiement')]
    public function deleteMethodePaiement(Request $request, $methode)
    {
        $methodePaiement = $this->methodePaiementRepo->findOneBy(['id' => $methode]);

        $data = [];
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->comptabiliteService->remove($methodePaiement);
                $this->comptabiliteService->update();
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
            }
                
        }catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }
}
