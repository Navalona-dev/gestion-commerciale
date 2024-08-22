<?php

namespace App\Controller\Admin;

use App\Entity\Affaire;
use App\Entity\Facture;
use App\Entity\FactureEcheance;
use App\Service\ProductService;
use App\Form\AddFactureEcheanceType;
use App\Form\StatutFactureEcheanceType;
use App\Service\FactureEcheanceService;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/facture/echeance', name: 'factures_echeance')]
class FactureEcheanceController extends AbstractController
{
    private $factureEcheanceService;
    private $em;
    private $productService;

    public function __construct(
        FactureEcheanceService $factureEcheanceService,
        EntityManagerInterface $em,
        ProductService $productService
    )
    {
        $this->factureEcheanceService = $factureEcheanceService;
        $this->em = $em;
        $this->productService = $productService;
    }

    #[Route('/{affaire}', name: '_create')]
    public function new(Affaire $affaire, Request $request): Response
    {
        $request->getSession()->set('idAffaire', $affaire->getId());

        $data = [];

        try {
            $produits = $this->productService->findProduitAffaire($affaire);
            if ($produits == false) {
                $produits = [];
            }

            $form = $this->createForm(AddFactureEcheanceType::class, null);
            $form->handleRequest($request);

            $facture = null;

            $totalPayer = 0;
            $montant = 0;

            if($form->isSubmitted() && $form->isValid()) {
                $montantHt = $request->request->get('montantHt');

                if ($request->isXmlHttpRequest()) {
                    //$facture = $this->factureEcheanceService->add($affaire, $request);
                    $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/factures/echeance/';
                    list($pdfContent, $facture) = $this->factureEcheanceService->add($affaire, $request, $documentFolder, $form, $montant, $totalPayer);
                
                    // Utiliser le numéro de la facture pour le nom du fichier
                    //$filename = "Facture(FA-" . $facture->getNumero() . ").pdf";
                    $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
                    $pdfPath = '/uploads/factures/echeance/' . $filename;
                    file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                    // Retourner le PDF en réponse
                    return new JsonResponse([
                        'status' => 'success',
                        'pdfUrl' => $pdfPath,
                    ]);

                    
                }
                if ($totalPayer > $montantHt) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le total des montants sur les échéances et avances ne doit pas dépasser le montant à payer.'], Response::HTTP_OK);
                    
                } elseif ($totalPayer < $montantHt)
                {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le total des montants sur les échéances et avances doit être égale au montant à payer.'], Response::HTTP_OK);
                }
                
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/facture_echeance/modal_new.html.twig', [
                'facture' => $facture,
                'affaire' => $affaire,
                'form' => $form->createView(),
                'produits' => $produits
            ]);
           
            return new JsonResponse($data);

        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        }
    }

    #[Route('/liste/{facture}', name: '_liste')]
    public function liste(Facture $facture, Request $request): Response
    {
        $request->getSession()->set('idFacture', $facture->getId());

        $data = [];

        try {

            $factureEcheances = $facture->getFactureEcheances();

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/facture_echeance/index.html.twig', [
               'affaire' => $facture->getAffaire(),
               'listes' => $factureEcheances
            ]);
           
            return new JsonResponse($data);

        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        }
    }

    #[Route('/facture/{factureEcheance}', name: '_facture')]
    public function facture(FactureEcheance $factureEcheance, Request $request): Response
    {
        $data = [];

        try {

            $form = $this->createForm(StatutFactureEcheanceType::class, $factureEcheance);
            $form->handleRequest($request);

            $facture = $factureEcheance->getFacture();
            $affaire = $facture->getAffaire();

            $montant = $factureEcheance->getMontant();
            $avance = $facture->getReglement();
            $reglement = $avance + $montant;
            $montantHt = $facture->getSolde();

            $reste = $montantHt - $reglement;

            if($form->isSubmitted() && $form->isValid()) {

                $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/factures/echeance/';
                list($pdfContent, $newFacture) = $this->factureEcheanceService->addNewFacture($factureEcheance, $form, $reglement, $reste, $montant, $documentFolder);
                
                // Utiliser le numéro de la facture pour le nom du fichier
                //$filename = "Facture(FA-" . $facture->getNumero() . ").pdf";
                $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $newFacture->getNumero() . ".pdf";
                $pdfPath = '/uploads/factures/echeance/' . $filename;
                file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                // Retourner le PDF en réponse
                return new JsonResponse([
                    'status' => 'success',
                    'pdfUrl' => $pdfPath,
                ]);
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/facture_echeance/facture.html.twig', [
               'affaire' => $affaire,
               'facture' => $facture,
               'factureEcheance' => $factureEcheance,
               'form' => $form->createView(),
               'reste' => $reste
            ]);
           
            return new JsonResponse($data);

        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        }
    }
   
}
