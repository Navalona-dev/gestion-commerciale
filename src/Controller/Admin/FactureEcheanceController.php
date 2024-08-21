<?php

namespace App\Controller\Admin;

use App\Entity\Affaire;
use App\Entity\FactureEcheance;
use App\Service\ProductService;
use App\Form\AddFactureEcheanceType;
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
                    $facture = $this->factureEcheanceService->add($affaire, $request);
                    $formData = $form->getData();
                    $reglement = $formData->getReglement();

                    $facture->setReglement($reglement);
                    $this->em->persist($facture);

                    $formDataEcheance = $form->get('factureEcheances')->getData();
                    $factureEcheances = $formDataEcheance;

                    foreach($factureEcheances as $factureEcheance) {

                        $factureEcheance->setStatus('encours');
                        $factureEcheance->setDateCreation(new \DateTime());
                        $factureEcheance->setFacture($facture);

                        $this->em->persist($factureEcheance);

                        $montant += $factureEcheance->getMontant();

                        $totalPayer = $montant + $reglement;

                    }
                    
                }
                if ($totalPayer > $montantHt) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le total des montants sur les échéances et avances ne doit pas dépasser le montant à payer.'], Response::HTTP_OK);
                    
                } elseif ($totalPayer < $montantHt)
                {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le total des montants sur les échéances et avances doit être égale au montant à payer.'], Response::HTTP_OK);
                }


                $this->em->flush();
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
}
