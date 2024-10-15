<?php

namespace App\Controller\Admin;

use App\Entity\Affaire;
use App\Entity\Facture;
use App\Entity\FactureEcheance;
use App\Service\ProductService;
use App\Form\FactureEcheanceType;
use App\Form\AddFactureEcheanceType;
use App\Repository\FactureRepository;
use App\Form\StatutFactureEcheanceType;
use App\Service\FactureEcheanceService;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\FactureEcheanceReporterType;
use App\Service\ApplicationManager;
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
    private $factureRepository;
    private $application;

    public function __construct(
        FactureEcheanceService $factureEcheanceService,
        EntityManagerInterface $em,
        ProductService $productService,
        FactureRepository $factureRepository,
        ApplicationManager $applicationManager
    )
    {
        $this->factureEcheanceService = $factureEcheanceService;
        $this->em = $em;
        $this->productService = $productService;
        $this->factureRepository = $factureRepository;
        $this->application = $applicationManager->getApplicationActive();
    }

    #[Route('/{affaire}', name: '_create')]
    public function new(Affaire $affaire, Request $request): Response
    {
        $request->getSession()->set('idAffaire', $affaire->getId());
        $applicationRevendeur = $affaire->getApplicationRevendeur();

        $data = [];

        try {
            $produits = $this->productService->findProduitAffaire($affaire);
            if ($produits == false) {
                $produits = [];
            }

            $form = $this->createForm(AddFactureEcheanceType::class, null);
            $form->handleRequest($request);

            $facture = null;
            $montant = 0;
            $totalPayer = 0;

            if($form->isSubmitted() && $form->isValid()) {
                $montantHt = $request->request->get('montantHt');

                if ($request->isXmlHttpRequest()) {
                    $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/valide/';
                     // Chemin du dossier des échéances
                    $echeanceFolder = $this->getParameter('kernel.project_dir') . '/public/uploads/APP_'.$this->application->getId().'/factures/echeance/';
                    
                    // Vérifier si le dossier des échéances existe, sinon le créer
                    if (!is_dir($echeanceFolder)) {
                        if (!mkdir($echeanceFolder, 0775, true)) {
                            return new JsonResponse(['status' => 'error', 'message' => 'Le dossier des échéances ne peut pas être créé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    }
                    list($pdfContent, $facture, $totalPayer) = $this->factureEcheanceService->add($affaire, $request, $documentFolder, $form, $montant, $totalPayer, $montantHt, $applicationRevendeur);
                
                    // Utiliser le numéro de la facture pour le nom du fichier
                    $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
                    $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/echeance/' . $filename;
                    file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                    // Retourner le PDF en réponse

                    if ($totalPayer > $montantHt) {
                        return new JsonResponse(['status' => 'error', 'message' => 'Le total des montants sur les échéances et avances ne doit pas dépasser le montant à payer.'], Response::HTTP_OK);
                        
                    } elseif ($totalPayer < $montantHt)
                    {
                        return new JsonResponse(['status' => 'error', 'message' => 'Le total des montants sur les échéances et avances doit être égale au montant à payer.'], Response::HTTP_OK);
                    }

                    return new JsonResponse([
                        'status' => 'success',
                        'pdfUrl' => $pdfPath,
                    ]);
                    
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

            $factureEcheances = $facture->getFactureEcheances()->toArray();

            $montantHt = 0;
            $solde = $facture->getSolde();

            foreach($factureEcheances as $factureEcheance) {
                $montantHt += $factureEcheance->getMontant();
            }

            if($facture->getAvance() != null || $facture->getAvance() > 0) {
                $montantHt = $montantHt + $facture->getAvance();
            } 

            $montantManquant = $facture->getSolde() - $montantHt;
            
            $error = false;

            if($montantHt != $solde) {
                $error = true;
            }

            // Trier par ID croissant
            usort($factureEcheances, function($a, $b) {
                return $a->getId() <=> $b->getId();
            });

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/facture_echeance/index.html.twig', [
               'affaire' => $facture->getAffaire(),
               'listes' => $factureEcheances,
               'facture' => $facture,
               'error' => $error,
               'montantHt' => $montantHt,
               'montantManquant' => $montantManquant,
               'application' => $this->application
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

            $montant = 0;
            $reste = 0;
            $avance = 0;
            $factureEcheanceReglement = 0;

            if($facture->getReglement() == null){
                $avance = 0;
                $factureEcheanceReglement = 0;
            }else {
                $avance = $facture->getReglement();
                $factureEcheanceReglement = $factureEcheance->getReglement();
            }
            $montantHt = $facture->getSolde();     
            $reglement = 0; 

            if(($factureEcheance->isReporter() == null || $factureEcheance->isReporter() == 0) && ($factureEcheanceReglement + $factureEcheance->getMontant() == $facture->getSolde()) ) {
                $reste = 0;
                $reglement = $facture->getSolde();
            } else {
                if($factureEcheance->getReglement() == null) {
                    $montant = $factureEcheance->getMontant();
                } else {
                    if($factureEcheance->getReglement() > $factureEcheance->getMontant()) {
                        $montant = $factureEcheance->getReglement() - $factureEcheance->getMontant();
                        
                    } elseif($factureEcheance->getMontant() > $factureEcheance->getReglement())
                    {
                        $montant = $factureEcheance->getMontant() - $factureEcheance->getReglement();
                    }
                   // $reglement = $avance + $montant;   
                }
            }
            $reglement = $avance + $montant;

            $reste = $montantHt - $reglement;

            if($form->isSubmitted() && $form->isValid()) {

                $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/echeance/';
                $echeanceFolder = $this->getParameter('kernel.project_dir') . '/public/uploads/APP_'.$this->application->getId().'/factures/echeance/';
                    
                // Vérifier si le dossier des échéances existe, sinon le créer
                if (!is_dir($echeanceFolder)) {
                    if (!mkdir($echeanceFolder, 0775, true)) {
                        return new JsonResponse(['status' => 'error', 'message' => 'Le dossier des échéances ne peut pas être créé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
                list($pdfContent, $newFacture) = $this->factureEcheanceService->addNewFacture($factureEcheance, $form, $reglement, $reste, $montant, $documentFolder);
                
                // Utiliser le numéro de la facture pour le nom du fichier
                //$filename = "Facture(FA-" . $facture->getNumero() . ").pdf";
                $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $newFacture->getNumero(). '-' . $newFacture->getEcheanceNumero() . ".pdf";
                $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/echeance/' . $filename;
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

    #[Route('/facture/reporter/{factureEcheance}', name: '_facture_reporter')]
    public function factureReporter(FactureEcheance $factureEcheance, Request $request): Response
    {
        $data = [];

        try {

            $form = $this->createForm(FactureEcheanceReporterType::class, $factureEcheance);
            $form->handleRequest($request);

            $facture = $factureEcheance->getFacture();
            $affaire = $facture->getAffaire();

            $montant = 0;
            $reste = 0;
            $avance = 0;
            if($facture->getReglement() == null){
                $avance = 0;
            }else {
                $avance = $facture->getReglement();
            }
            $montantHt = $facture->getSolde();     
            $reglement = 0; 

            if($factureEcheance->getReglement() == null) {
                $reste = $montantHt - $avance;
                
            } else {
                if($factureEcheance->getReglement() > $factureEcheance->getMontant()) {
                    $montant = $factureEcheance->getReglement() - $factureEcheance->getMontant();
                    
                } elseif($factureEcheance->getMontant() > $factureEcheance->getReglement())
                {
                    $montant = $factureEcheance->getMontant() - $factureEcheance->getReglement();
                }
                $reglement = $avance + $montant;   
                $reste = $montantHt - $reglement + $factureEcheance->getReglement();
                
            }


            if($form->isSubmitted() && $form->isValid()) {
                $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/echeance/';
                $echeanceFolder = $this->getParameter('kernel.project_dir') . '/public/uploads/APP_'.$this->application->getId().'/factures/echeance/';
                    
                // Vérifier si le dossier des échéances existe, sinon le créer
                if (!is_dir($echeanceFolder)) {
                    if (!mkdir($echeanceFolder, 0775, true)) {
                        return new JsonResponse(['status' => 'error', 'message' => 'Le dossier des échéances ne peut pas être créé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
                list($pdfContent, $newFacture, $reglementEcheance) = $this->factureEcheanceService->factureReporter($factureEcheance, $form, $documentFolder);
                
                // Utiliser le numéro de la facture pour le nom du fichier
                //$filename = "Facture(FA-" . $facture->getNumero() . ").pdf";
                $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $newFacture->getNumero() . ".pdf";
                $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/echeance/' . $filename;
                file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                if($reglementEcheance > $factureEcheance->getMontant()) {
                    return new JsonResponse([
                        'status' => 'error'
                    ]
                    );
                }
                // Retourner le PDF en réponse
                return new JsonResponse([
                    'status' => 'success',
                    'pdfUrl' => $pdfPath,
                ]);
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/facture_echeance/modal_reporter.html.twig', [
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

    #[Route('/nouveau/{facture}', name: '_add')]
    public function add(Request $request, Facture $facture): Response
    {
        $data = [];

        try {

            $factureEcheances = $facture->getFactureEcheances();
            $factureEcheanceFirst = $facture->getFactureEcheances()[0];
            $montantFirst = $factureEcheanceFirst->getMontant();

            $newFactureEcheance = new FactureEcheance();

            $montantHt = 0;
            $reste = 0;

            foreach($factureEcheances as $factureEcheance) {
                $montantHt += $factureEcheance->getMontant();
            }

            if($facture->getAvance()) {
                $reste = $facture->getSolde() - $montantHt - $facture->getAvance();

            } else {
                $reste = $facture->getSolde() - $montantHt;
            }


            $form = $this->createForm(FactureEcheanceType::class, null);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $montantData = $formData->getMontant();

                if ($request->isXmlHttpRequest()) {
                    
                    list($newFactureEcheance, $error) = $this->factureEcheanceService->nouveauEcheance($newFactureEcheance, $facture, $form);
                    if($error){
                        return new JsonResponse([
                            'status' => 'error',
                            'message' => 'Le montant à ajouter ne doit pas dépasser le montant total du facture'
                        ]
                        );
                    } else {
                        return new JsonResponse([
                            'status' => 'success'
                        ]);
                    }
                }
                
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/facture_echeance/modal_add.html.twig', [
               'form' => $form->createView(),
               'facture' => $facture,
               'montantFirst' => $montantFirst,
               'reste' => $reste
            ]);
           
            return new JsonResponse($data);

        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        }
    }

    #[Route('/edit/{factureEcheance}', name: '_edit')]
    public function edit(Request $request, FactureEcheance $factureEcheance): Response
    {
        $data = [];

        try {

            $facture = $factureEcheance->getFacture();

            $form = $this->createForm(FactureEcheanceType::class, $factureEcheance);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {

                if ($request->isXmlHttpRequest()) {
                    
                    list($factureEcheance, $error) = $this->factureEcheanceService->edit($factureEcheance);
                    
                    if($error) {
                        return new JsonResponse([
                            'status' => 'error',
                            'message' => 'Le montant que vous avez ajouter donne un resultat qui dépasse le solde total ' . number_format($facture->getSolde(), 2, ',', ' ') . ' Ar, vous devez changer cela!!'
                        ]);
                    } else {
                        return new JsonResponse([
                            'status' => 'success'
                        ]);
                    }
                }
                
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/facture_echeance/modal_update.html.twig', [
               'form' => $form->createView(),
               'facture' => $facture,
               'factureEcheance' => $factureEcheance,
            ]);
           
            return new JsonResponse($data);

        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        }
    }

    #[Route('/delete/{factureEcheance}', name: '_delete')]
    public function delete(Request $request, FactureEcheance $factureEcheance)
    {
     
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->factureEcheanceService->remove($factureEcheance);
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
            }
                
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }
   
}
