<?php

namespace App\Controller\Admin;

use App\Entity\Facture;
use App\Entity\Benefice;
use App\Entity\Comptabilite;
use App\Form\ComptabiliteType;
use App\Entity\MethodePaiement;
use App\Form\MethodePaiementType;
use App\Service\ApplicationManager;
use App\Service\ComptabiliteService;
use App\Repository\DepenseRepository;
use App\Repository\FactureRepository;
use App\Repository\BeneficeRepository;
use App\Repository\ComptabiliteRepository;
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
    private $beneficeRepo;
    private $application;
    private $comptabiliteRepository;

    public function __construct(
        DepenseRepository $depenseRepository,
        FactureRepository $factureRepository,
        ComptabiliteService $comptabiliteService,
        MethodePaiementRepository $methodePaiementRepo,
        ApplicationManager $applicationManager,
        BeneficeRepository $beneficeRepo,
        ComptabiliteRepository $comptabiliteRepository
    )
    {
        $this->depenseRepository = $depenseRepository;
        $this->factureRepository = $factureRepository;
        $this->comptabiliteService = $comptabiliteService;
        $this->methodePaiementRepo = $methodePaiementRepo;
        $this->beneficeRepo = $beneficeRepo;
        $this->application = $applicationManager->getApplicationActive();
        $this->comptabiliteRepository = $comptabiliteRepository;
    }

    #[Route('/', name: '_show')]
    public function index(Request $request): Response
    {
        $request->getSession()->set('typePage', 'compta');

        $data = [];
        try {
            $existDate = false;
    
            // Récupérer les paramètres de la requête (filtres)
            $dateFilterCommande = \DateTime::createFromFormat('d/m/Y', $request->get('filter-commande'));
            $dateFilterDepense = \DateTime::createFromFormat('d/m/Y', $request->get('filter-depense'));
            $factures = null;
            $depenses = null;
            $filter = $request->get('filter');
            $dateFacture = null;
            $methodePaiements = null;

            $request->getSession()->set('dateFilterCommande', $dateFilterCommande);

            // Si un filtre pour les factures est défini, appliquer le filtre
            if ($dateFilterCommande) {
                $dateFacture = $dateFilterCommande->format('d-m-Y');
                $factures = $this->factureRepository->selectFactureByDate('regle', $dateFilterCommande);
                $methodePaiements = $this->methodePaiementRepo->selectMethodeByDate($dateFilterCommande);
            }else {
                $factures = $this->factureRepository->selectFactureToday('regle');
                $methodePaiements = $this->methodePaiementRepo->selectMethodeToday();

            }
    
            // Si un filtre pour les dépenses est défini, appliquer le filtre
            if ($dateFilterDepense) {
                $depenses = $this->depenseRepository->selectDepenseByDate($dateFilterDepense);
            } else {
                $depenses = $this->depenseRepository->selectDepenseToday();
            }
    
            // Gestion des dates de bénéfices
            $dateToday = new \DateTime();
            $dateTodayFormat = $dateToday->format('d-m-Y');

            $benefices = $this->beneficeRepo->findAllDate();
            $dateBenefices = [];
            foreach ($benefices as $benefice) {
                $dateBenefices[] = $benefice['dateBenefice']->format('d-m-Y');
            }
    
            // Vérification si la date d'aujourd'hui existe dans les bénéfices
            if (in_array($dateTodayFormat, $dateBenefices) || in_array($dateFacture, $dateBenefices)) {
                $existDate = true;
            }
    
            // Rendu du template avec les données nécessaires
            $data["html"] = $this->renderView('admin/comptabilite/index.html.twig', [
                'methodePaiements' => $methodePaiements,
                'existDate' => $existDate,
                'factures' => $factures,
                'depenses' => $depenses,
                'filter' => $filter,
                
            ]);
    
            // Retour de la réponse JSON avec le HTML généré
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            // Gestion des exceptions
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }

    #[Route('/reload', name: '_reload')]
    public function reload(Request $request): Response
    {
        $request->getSession()->set('typePage', 'compta');

        $data = [];
        try {
            $existDate = false;
    
            // Récupérer les paramètres de la requête (filtres)
            $dateFilterCommande = \DateTime::createFromFormat('d/m/Y', $request->get('filter-commande'));
            $dateFilterDepense = \DateTime::createFromFormat('d/m/Y', $request->get('filter-depense'));
            $factures = null;
            $depenses = null;
            $filter = $request->get('filter');
            $dateFacture = null;

            $methodePaiements = null;

            $request->getSession()->set('dateFilterCommande', $dateFilterCommande);

            // Si un filtre pour les factures est défini, appliquer le filtre
            if ($dateFilterCommande) {
                $dateFacture = $dateFilterCommande->format('d-m-Y');
                $factures = $this->factureRepository->selectFactureByDate('regle', $dateFilterCommande);
                $methodePaiements = $this->methodePaiementRepo->selectMethodeByDate($dateFilterCommande);
            }else {
                $factures = $this->factureRepository->selectFactureToday('regle');
                $methodePaiements = $this->methodePaiementRepo->selectMethodeToday();

            }

            //dd($dateFilterCommande, $factures);
    
            // Si un filtre pour les dépenses est défini, appliquer le filtre
            if ($dateFilterDepense) {
                $depenses = $this->depenseRepository->selectDepenseByDate($dateFilterDepense);
            } else {
                $depenses = $this->depenseRepository->selectDepenseToday();
            }
    
    
            // Gestion des dates de bénéfices
            $dateToday = new \DateTime();
            $dateTodayFormat = $dateToday->format('d-m-Y');
    
            $benefices = $this->beneficeRepo->findAllDate();
            $dateBenefices = [];
            foreach ($benefices as $benefice) {
                $dateBenefices[] = $benefice['dateBenefice']->format('d-m-Y');
            }
    
            // Vérification si la date d'aujourd'hui existe dans les bénéfices
            if (in_array($dateTodayFormat, $dateBenefices) || in_array($dateFacture, $dateBenefices)) {
                $existDate = true;
            }
    
            // Rendu du template avec les données nécessaires
            $data["html"] = $this->renderView('admin/comptabilite/reload_compta.html.twig', [
                'methodePaiements' => $methodePaiements,
                'existDate' => $existDate,
                'factures' => $factures,
                'depenses' => $depenses,
                'filter' => $filter
            ]);
    
            // Retour de la réponse JSON avec le HTML généré
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            // Gestion des exceptions
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }
    
    #[Route('/liste', name: '_liste')]
    public function liste(Request $request): Response
    {
        $data = [];
        try {
        
            $comptabilites = $this->comptabiliteRepository->findAllByApplication(); 

            // Rendu du template avec les données nécessaires
            $data["html"] = $this->renderView('admin/comptabilite/liste.html.twig', [
                'comptabilites' => $comptabilites,
            ]);
    
            // Retour de la réponse JSON avec le HTML généré
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            // Gestion des exceptions
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }

    #[Route('/new', name: '_create')]
    public function create(Request $request)
    {
        $comptabilite = new Comptabilite();
        $beneficeId = $request->getSession()->get('beneficeId');

        $form = $this->createForm(ComptabiliteType::class, $comptabilite);
        $data = [];
        try {

            $form->handleRequest($request);

            $totalDepense = $request->getSession()->get('totalDepense');
            $totalBenefice = $request->getSession()->get('totalBenefice');

            $benefice = $this->beneficeRepo->findOneBy(['id' => $beneficeId]);

            $dateBenefice = $benefice->getDateBenefice();
            $depenses = $this->depenseRepository->selectDepenseByDate($dateBenefice);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {

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

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/comptabilite/new.html.twig', [
                'form' => $form->createView(),
                'totalDepense' => $totalDepense,
                'totalBenefice' => $totalBenefice,
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

    #[Route('/detail/paiement/{facture}', name: '_detail_paiement')]
    public function detailPaiement(Facture $facture, Request $request): Response
    {
        $typePage = $request->getSession()->get('typePage');
        //dd($typePage);

        $data = [];
        try {

            $methodePaiements = $facture->getMethodePaiements();

            $data["html"] = $this->renderView('admin/comptabilite/detail_methode_paiement.html.twig', [
                'methodePaiements' => $methodePaiements,
                'facture' => $facture,
                'typePage' => $typePage
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
                'methodePaiement' => $methodePaiement,
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

    #[Route('/detail/{idComptabilite}', name: '_detail_comptabilite')]
    public function detailCompta($idComptabilite, Request $request): Response
    {
        $request->getSession()->set('typePage', 'detail');
        $request->getSession()->set('idComptabilite', $idComptabilite);
        $idBenefice = $request->getSession()->get('beneficeId');

        $data = [];
        try {
        
            //$benefice = $this->beneficeRepo->findOneBy(['id' => $idBenefice]);
            $comptabilite = $this->comptabiliteRepository->findOneBy(['id' => $idComptabilite]);
            $depenses = $comptabilite->getDepenses();
            //dd($comptabilite, count($depenses));
            $benefice = $this->beneficeRepo->findOneBy(['id' => $idBenefice]);

            $request->getSession()->set('existeCompta', $comptabilite);

            // Rendu du template avec les données nécessaires
            $data["html"] = $this->renderView('admin/comptabilite/detail_comptabilite.html.twig', [
                'comptabilite' => $comptabilite,
                'depenses' => $depenses,
                'benefice' => $benefice
            ]);
    
            // Retour de la réponse JSON avec le HTML généré
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            // Gestion des exceptions
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }
}
