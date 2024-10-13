<?php

namespace App\Controller\Admin;

use App\Entity\Facture;
use App\Entity\MethodePaiement;
use App\Form\MethodePaiementType;
use App\Service\ComptabiliteService;
use App\Repository\DepenseRepository;
use App\Repository\FactureRepository;
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

    public function __construct(
        DepenseRepository $depenseRepository,
        FactureRepository $factureRepository,
        ComptabiliteService $comptabiliteService
    )
    {
        $this->depenseRepository = $depenseRepository;
        $this->factureRepository = $factureRepository;
        $this->comptabiliteService = $comptabiliteService;
    }

    #[Route('/', name: '_liste')]
    public function index(): Response
    {
        $data = [];
        try {

            $depensesToday = $this->depenseRepository->selectDepenseToday();
            $facturesToday = $this->factureRepository->selectFactureToday('regle');

            $data["html"] = $this->renderView('admin/comptabilite/index.html.twig', [
                //'listes' => $affaires,
                'depensesToday' => $depensesToday,
                'facturesToday' => $facturesToday,
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
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
                'facture' => $facture
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
}
