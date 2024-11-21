<?php

namespace App\Controller\Admin;

use App\Form\AdminType;
use App\Service\AccesService;
use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use App\Service\DashboardService;
use App\Repository\TypeRepository;
use App\Repository\AdminRepository;
use App\Service\ApplicationManager;
use App\Service\HeaderDataProvider;
use App\Repository\ContactRepository;
use App\Repository\MessageRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Form\FormError;
use App\Repository\CategoryRepository;
use App\Repository\SocialLinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DashboardController extends AbstractController
{

    private $categoryPermissionService;
    private $accesService;
    private $application;
    private $dashboardService;

    public function __construct(
        ApplicationManager $applicationManager, 
        AccesService $accesService,
        DashboardService $dashboardService)
    {
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
        $this->dashboardService = $dashboardService;
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(
        Request $request, 
        HeaderDataProvider $headerDataProvider,
        SessionInterface $session)
    {
        $anchorName = $request->request->get('anchorName');
        $headerData = $headerDataProvider->getHeaderData();
        $idAffaire = $session->get('idAffaire');
        $idCompte = $session->get('idCompte');
        $idProduit = $session->get('produitCategorieId');
        $idFacture = $session->get('idFacture');
        $idBenefice = $session->get('beneficeId');
        $idComptabilite = $session->get('idComptabilite');

        //count commande
        $countAffaireToday = $this->dashboardService->getCountAffairesToday('paye', 'commande');
        $countAffaireYesterday = $this->dashboardService->getCountAffairesYesterday('paye', 'commande');
        $countAffaireThisWeek = $this->dashboardService->getCountAffairesThisWeek('paye', 'commande');
        $countAffaireLastWeek = $this->dashboardService->getCountAffairesLastWeek('paye', 'commande');
        $countAffaireThisMonth = $this->dashboardService->getCountAffairesThisMonth('paye', 'commande');
        $countAffaireLastMonth = $this->dashboardService->getCountAffairesLastMonth('paye', 'commande');
        $countAffaireThisYear = $this->dashboardService->getCountAffairesThisYear('paye', 'commande');
        $countAffaireLastYear = $this->dashboardService->getCountAffairesLastYear('paye', 'commande');

        //count produit
        $countProduitToday = $this->dashboardService->getCountProductsToday();
        $countProduitYesterday = $this->dashboardService->getCountProductsYesterday();
        $countProduitThisWeek = $this->dashboardService->getCountProductsThisWeek();
        $countProduitLastWeek = $this->dashboardService->getCountProductsLastWeek();
        $countProduitThisMonth = $this->dashboardService->getCountProductsThisMonth();
        $countProduitLastMonth = $this->dashboardService->getCountProductsLastMonth();
        $countProduitThisYear = $this->dashboardService->getCountProductsThisYear();
        $countProduitLastYear = $this->dashboardService->getCountProductsLastYear();

        //count stock
        $countStock = $this->dashboardService->getCountStocks();
        $countStockToday = $this->dashboardService->getCountStockToday();
        $countStockYesterday = $this->dashboardService->getCountStockYesterday();
        $countStockThisWeek = $this->dashboardService->getCountStockThisWeek();
        $countStockLastWeek = $this->dashboardService->getCountStockLastWeek();
        $countStockThisMonth = $this->dashboardService->getCountStockThisMonth();
        $countStockLastMonth = $this->dashboardService->getCountStockLastMonth();
        $countStockThisYear = $this->dashboardService->getCountStockThisYear();
        $countStockLastYear = $this->dashboardService->getCountStockLastYear();

        //count stock restant
        $countStockRestant = $this->dashboardService->getCountStockRestant();
        $countStockRestantToday = $this->dashboardService->getCountStockRestantToday();
        $countStockRestantYesterday = $this->dashboardService->getCountStockRestantYesterday();
        $countStockRestantThisWeek = $this->dashboardService->getCountStockRestantThisWeek();
        $countStockRestantLastWeek = $this->dashboardService->getCountStockRestantLastWeek();
        $countStockRestantThisMonth = $this->dashboardService->getCountStockRestantThisMonth();
        $countStockRestantLastMonth = $this->dashboardService->getCountStockRestantLastMonth();
        $countStockRestantThisYear = $this->dashboardService->getCountStockRestantThisYear();
        $countStockRestantLastYear = $this->dashboardService->getCountStockRestantLastYear();

        //count stock vendu
        $countStockVenduToday = $this->dashboardService->getCountStockVenduToday('paye', 'commande');
        $countStockVenduYesterday = $this->dashboardService->getCountStockVenduYesterday('paye', 'commande');
        $countStockVenduThisWeek = $this->dashboardService->getCountStockVenduThisWeek('paye', 'commande');
        $countStockVenduLastWeek = $this->dashboardService->getCountStockVenduLastWeek('paye', 'commande');
        $countStockVenduThisMonth = $this->dashboardService->getCountStockVenduThisMonth('paye', 'commande');
        $countStockVenduLastMonth = $this->dashboardService->getCountStockVenduLastMonth('paye', 'commande');
        $countStockVenduThisYear = $this->dashboardService->getCountStockVenduThisYear('paye', 'commande');
        $countStockVenduLastYear = $this->dashboardService->getCountStockVenduLastYear('paye', 'commande');

        //count client
        $countClientToday = $this->dashboardService->getCountCompteToday(1);
        $countClientYesterday = $this->dashboardService->getCountCompteYesterday(1);
        $countClientThisWeek = $this->dashboardService->getCountCompteThisWeek(1);
        $countClientLastWeek = $this->dashboardService->getCountCompteLastWeek(1);
        $countClientThisMonth = $this->dashboardService->getCountCompteThisMonth(1);
        $countClientLastMonth = $this->dashboardService->getCountCompteLastMonth(1);
        $countClientThisYear = $this->dashboardService->getCountCompteThisYear(1);
        $countClientLastYear = $this->dashboardService->getCountCompteLastYear(1);

        //count fournisseur
        $countFournisseurToday = $this->dashboardService->getCountCompteToday(2);
        $countFournisseurYesterday = $this->dashboardService->getCountCompteYesterday(2);
        $countFournisseurThisWeek = $this->dashboardService->getCountCompteThisWeek(2);
        $countFournisseurLastWeek = $this->dashboardService->getCountCompteLastWeek(2);
        $countFournisseurThisMonth = $this->dashboardService->getCountCompteThisMonth(2);
        $countFournisseurLastMonth = $this->dashboardService->getCountCompteLastMonth(2);
        $countFournisseurThisYear = $this->dashboardService->getCountCompteThisYear(2);
        $countFournisseurLastYear = $this->dashboardService->getCountCompteLastYear(2);

        //count transfert
        $countTransfertToday = $this->dashboardService->getCountTransfertToday();
        $countTransfertYesterday = $this->dashboardService->getCountTransfertYesterday();
        $countTransfertThisWeek = $this->dashboardService->getCountTransfertThisWeek();
        $countTransfertLastWeek = $this->dashboardService->getCountTransfertLastWeek();
        $countTransfertThisMonth = $this->dashboardService->getCountTransfertThisMonth();
        $countTransfertLastMonth = $this->dashboardService->getCountTransfertLastMonth();
        $countTransfertThisYear = $this->dashboardService->getCountTransfertThisYear();
        $countTransfertLastYear = $this->dashboardService->getCountTransfertLastYear();

        //chart
        $countOrderByDayThisWeek = $this->dashboardService->getOrdersCountByDayThisWeek('paye', 'commande');
        $countOrderByDayLastWeek = $this->dashboardService->getOrdersCountByDayLastWeek('paye', 'commande');
        $countOrderByMonthThisYear = $this->dashboardService->getOrdersCountThisYearByMonth('paye', 'commande');
        $countOrderByMonthLastYear = $this->dashboardService->getOrdersCountLastYearByMonth('paye', 'commande');

        $countClientByDayThisWeek = $this->dashboardService->getClientsCountByDayThisWeek(1);
        $countClientByDayLastWeek = $this->dashboardService->getClientsCountByDayLastWeek(1);
        $countClientByDayThisYear = $this->dashboardService->getClientsRegisteredThisYearByMonth(1);
        $countClientByDayLastYear = $this->dashboardService->getClientsRegisteredLastYearByMonth(1);

        $countProductsSoldThisWeekByDay = $this->dashboardService->getProductsSoldThisWeekByDay('paye', 'commande');
        $countProductsSoldLastWeekByDay = $this->dashboardService->getProductsSoldLastWeekByDay('paye', 'commande');
        $countProductsSoldThisYearByDay = $this->dashboardService->getProductsSoldThisYearByMonth('paye', 'commande');
        $countProductsSoldLastYearByDay = $this->dashboardService->getProductsSoldLastYearByMonth('paye', 'commande');
        //best order
        $bestOrderToday = $this->dashboardService->getTopOrdersByTotalToday('paye', 'commande');
        $bestOrderYesterday = $this->dashboardService->getTopOrdersByTotalYesterday('paye', 'commande');
        $bestOrderThisWeek = $this->dashboardService->getTopOrdersByTotalThisWeek('paye', 'commande');
        $bestOrderLastWeek = $this->dashboardService->getTopOrdersByTotalLastWeek('paye', 'commande');
        $bestOrderThisMonth = $this->dashboardService->getTopOrdersByTotalThisMonth('paye', 'commande');
        $bestOrderLastMonth = $this->dashboardService->getTopOrdersByTotalLastMonth('paye', 'commande');
        $bestOrderThisYear = $this->dashboardService->getTopOrdersByTotalThisYear('paye', 'commande');
        $bestOrderLastYear = $this->dashboardService->getTopOrdersByTotalLastYear('paye', 'commande');
        //dd($bestOrderYesterday);
        //dd($countStockRestantToday, $countStockRestantYesterday, $countStockRestantThisWeek, $countStockRestantLastWeek, $countStockRestantThisMonth, $countStockRestantLastMonth, $countStockRestantThisYear, $countStockRestantLastYear);
        
        $countProduitDatePeremption = $this->dashboardService->getCountProduitDatePeremptionProche();
        //dd($countProduitDatePeremption);
        $data = [];

        if ($request->isXmlHttpRequest()) {
            try {

                $_data = array_merge($headerData, [
                    'listes' => [],
                    'idAffaire' => $idAffaire,
                    'idCompte' => $idCompte,
                    'idProduit' => $idProduit,
                    'idFacture' => $idFacture,
                    'idBenefice' => $idBenefice,
                    'idComptabilite' => $idComptabilite,

                    //count commande
                    'countAffaireToday' => $countAffaireToday,
                    'countAffaireYesterday' => $countAffaireYesterday,
                    'countAffaireThisWeek' => $countAffaireThisWeek,
                    'countAffaireLastWeek' => $countAffaireLastWeek,
                    'countAffaireThisMonth' => $countAffaireThisMonth,
                    'countAffaireLastMonth' => $countAffaireLastMonth,
                    'countAffaireThisYear' => $countAffaireThisYear,
                    'countAffaireLastYear' => $countAffaireLastYear,

                    //count produit
                    'countProduitToday' => $countProduitToday,
                    'countProduitYesterday' => $countProduitYesterday,
                    'countProduitThisWeek' => $countProduitThisWeek,
                    'countProduitLastWeek' => $countProduitLastWeek,
                    'countProduitThisMonth' => $countProduitThisMonth,
                    'countProduitLastMonth' => $countProduitLastMonth,
                    'countProduitThisYear' => $countProduitThisYear,
                    'countProduitLastYear' => $countProduitLastYear,

                    //count stock
                    'countStock' => $countStock,
                    'countStockToday' => $countStockToday,
                    'countStockYesterday' => $countStockYesterday,
                    'countStockThisWeek' => $countStockThisWeek,
                    'countStockLastWeek' => $countStockLastWeek,
                    'countStockThisMonth' => $countStockThisMonth,
                    'countStockLastMonth' => $countStockLastMonth,
                    'countStockThisYear' => $countStockThisYear,
                    'countStockLastYear' => $countStockLastYear,

                    //count stock restant
                    'countStockRestant' => $countStockRestant,
                    'countStockRestantToday' => $countStockRestantToday,
                    'countStockRestantYesterday' => $countStockRestantYesterday,
                    'countStockRestantThisWeek' => $countStockRestantThisWeek,
                    'countStockRestantLastWeek' => $countStockRestantLastWeek,
                    'countStockRestantThisMonth' => $countStockRestantThisMonth,
                    'countStockRestantLastMonth' => $countStockRestantLastMonth,
                    'countStockRestantThisYear' => $countStockRestantThisYear,
                    'countStockRestantLastYear' => $countStockRestantLastYear,

                    //count stock vendu
                    'countStockVenduToday' => $countStockVenduToday,
                    'countStockVenduYesterday' => $countStockVenduYesterday,
                    'countStockVenduThisWeek' => $countStockVenduThisWeek,
                    'countStockVenduLastWeek' => $countStockVenduLastWeek,
                    'countStockVenduThisMonth' => $countStockVenduThisMonth,
                    'countStockVenduLastMonth' => $countStockVenduLastMonth,
                    'countStockVenduThisYear' => $countStockVenduThisYear,
                    'countStockVenduLastYear' => $countStockVenduLastYear,

                    //count client
                    'countClientToday' => $countClientToday,
                    'countClientYesterday' => $countClientYesterday,
                    'countClientThisWeek' => $countClientThisWeek,
                    'countClientLastWeek' => $countClientLastWeek,
                    'countClientThisMonth' => $countClientThisMonth,
                    'countClientLastMonth' => $countClientLastMonth,
                    'countClientThisYear' => $countClientThisYear,
                    'countClientLastYear' => $countClientLastYear,

                    //count fournisseur
                    'countFournisseurToday' => $countFournisseurToday,
                    'countFournisseurYesterday' => $countFournisseurYesterday,
                    'countFournisseurThisWeek' => $countFournisseurThisWeek,
                    'countFournisseurLastWeek' => $countFournisseurLastWeek,
                    'countFournisseurThisMonth' => $countFournisseurThisMonth,
                    'countFournisseurLastMonth' => $countFournisseurLastMonth,
                    'countFournisseurThisYear' => $countFournisseurThisYear,
                    'countFournisseurLastYear' => $countFournisseurLastYear,

                    //count transfert
                    'countTransfertToday' => $countTransfertToday,
                    'countTransfertYesterday' => $countTransfertYesterday,
                    'countTransfertThisWeek' => $countTransfertThisWeek,
                    'countTransfertLastWeek' => $countTransfertLastWeek,
                    'countTransfertThisMonth' => $countTransfertThisMonth,
                    'countTransfertLastMonth' => $countTransfertLastMonth,
                    'countTransfertThisYear' => $countTransfertThisYear,
                    'countTransfertLastYear' => $countTransfertLastYear,

                    //chart
                    'countOrderByDayThisWeek' => array_values($countOrderByDayThisWeek),
                    'countOrderByDayLastWeek' => array_values($countOrderByDayLastWeek),
                    'countOrderByMonthThisYear' => array_values($countOrderByMonthThisYear),
                    'countOrderByMonthLastYear' => array_values($countOrderByMonthLastYear),

                    'countClientByDayThisWeek' => array_values($countClientByDayThisWeek),
                    'countClientByDayLastWeek' => array_values($countClientByDayLastWeek),
                    'countClientByDayThisYear' => array_values($countClientByDayThisYear),
                    'countClientByDayLastYear' => array_values($countClientByDayLastYear),

                    'countProductsSoldThisWeekByDay' => array_values($countProductsSoldThisWeekByDay),
                    'countProductsSoldLastWeekByDay' => array_values($countProductsSoldLastWeekByDay),
                    'countProductsSoldThisYearByDay' => array_values($countProductsSoldThisYearByDay),
                    'countProductsSoldLastYearByDay' => array_values($countProductsSoldLastYearByDay),

                    //best order
                    'bestOrderToday' => $bestOrderToday,
                    'bestOrderYesterday' => $bestOrderYesterday,
                    'bestOrderThisWeek' => $bestOrderThisWeek,
                    'bestOrderLastWeek' => $bestOrderLastWeek,
                    'bestOrderThisMonth' => $bestOrderThisMonth,
                    'bestOrderLastMonth' => $bestOrderLastMonth,
                    'bestOrderThisYear' => $bestOrderThisYear,
                    'bestOrderLastYear' => $bestOrderLastYear,

                    //date peremption
                    'countProduitDatePeremption' => $countProduitDatePeremption,
                ]);
                
                $data["html"] = $this->renderView('admin/dashboard/index.html.twig', $_data);
            
                return new JsonResponse($data);
                
            } catch (\Exception $Exception) {
                $this->createNotFoundException('Exception' . $Exception->getMessage());
            }
            return new JsonResponse($data);
        } 

        $_data = array_merge($headerData, [
            'listes' => [],
            'idAffaire' => $idAffaire,
            'idCompte' => $idCompte,
            'idProduit' => $idProduit,
            'idFacture' => $idFacture,
            'idBenefice' => $idBenefice,
            'idComptabilite' => $idComptabilite,
            
            //count commande
            'countAffaireToday' => $countAffaireToday,
            'countAffaireYesterday' => $countAffaireYesterday,
            'countAffaireThisWeek' => $countAffaireThisWeek,
            'countAffaireLastWeek' => $countAffaireLastWeek,
            'countAffaireThisMonth' => $countAffaireThisMonth,
            'countAffaireLastMonth' => $countAffaireLastMonth,
            'countAffaireThisYear' => $countAffaireThisYear,
            'countAffaireLastYear' => $countAffaireLastYear,

            //count produit
            'countProduitToday' => $countProduitToday,
            'countProduitYesterday' => $countProduitYesterday,
            'countProduitThisWeek' => $countProduitThisWeek,
            'countProduitLastWeek' => $countProduitLastWeek,
            'countProduitThisMonth' => $countProduitThisMonth,
            'countProduitLastMonth' => $countProduitLastMonth,
            'countProduitThisYear' => $countProduitThisYear,
            'countProduitLastYear' => $countProduitLastYear,

            //count stock
            'countStock' => $countStock,
            'countStockToday' => $countStockToday,
            'countStockYesterday' => $countStockYesterday,
            'countStockThisWeek' => $countStockThisWeek,
            'countStockLastWeek' => $countStockLastWeek,
            'countStockThisMonth' => $countStockThisMonth,
            'countStockLastMonth' => $countStockLastMonth,
            'countStockThisYear' => $countStockThisYear,
            'countStockLastYear' => $countStockLastYear,

            //count stock restant
            'countStockRestant' => $countStockRestant,
            'countStockRestantToday' => $countStockRestantToday,
            'countStockRestantYesterday' => $countStockRestantYesterday,
            'countStockRestantThisWeek' => $countStockRestantThisWeek,
            'countStockRestantLastWeek' => $countStockRestantLastWeek,
            'countStockRestantThisMonth' => $countStockRestantThisMonth,
            'countStockRestantLastMonth' => $countStockRestantLastMonth,
            'countStockRestantThisYear' => $countStockRestantThisYear,
            'countStockRestantLastYear' => $countStockRestantLastYear,

             //count stock vendu
             'countStockVenduToday' => $countStockVenduToday,
             'countStockVenduYesterday' => $countStockVenduYesterday,
             'countStockVenduThisWeek' => $countStockVenduThisWeek,
             'countStockVenduLastWeek' => $countStockVenduLastWeek,
             'countStockVenduThisMonth' => $countStockVenduThisMonth,
             'countStockVenduLastMonth' => $countStockVenduLastMonth,
             'countStockVenduThisYear' => $countStockVenduThisYear,
             'countStockVenduLastYear' => $countStockVenduLastYear,

             //count client
             'countClientToday' => $countClientToday,
            'countClientYesterday' => $countClientYesterday,
            'countClientThisWeek' => $countClientThisWeek,
            'countClientLastWeek' => $countClientLastWeek,
            'countClientThisMonth' => $countClientThisMonth,
            'countClientLastMonth' => $countClientLastMonth,
            'countClientThisYear' => $countClientThisYear,
            'countClientLastYear' => $countClientLastYear,

             //count fournisseur
             'countFournisseurToday' => $countFournisseurToday,
             'countFournisseurYesterday' => $countFournisseurYesterday,
             'countFournisseurThisWeek' => $countFournisseurThisWeek,
             'countFournisseurLastWeek' => $countFournisseurLastWeek,
             'countFournisseurThisMonth' => $countFournisseurThisMonth,
             'countFournisseurLastMonth' => $countFournisseurLastMonth,
             'countFournisseurThisYear' => $countFournisseurThisYear,
             'countFournisseurLastYear' => $countFournisseurLastYear,

             //count transfert
             'countTransfertToday' => $countTransfertToday,
             'countTransfertYesterday' => $countTransfertYesterday,
             'countTransfertThisWeek' => $countTransfertThisWeek,
             'countTransfertLastWeek' => $countTransfertLastWeek,
             'countTransfertThisMonth' => $countTransfertThisMonth,
             'countTransfertLastMonth' => $countTransfertLastMonth,
             'countTransfertThisYear' => $countTransfertThisYear,
             'countTransfertLastYear' => $countTransfertLastYear,

             //chart
             'countOrderByDayThisWeek' => array_values($countOrderByDayThisWeek),
            'countOrderByDayLastWeek' => array_values($countOrderByDayLastWeek),
            'countOrderByMonthThisYear' => array_values($countOrderByMonthThisYear),
            'countOrderByMonthLastYear' => array_values($countOrderByMonthLastYear),

            'countClientByDayThisWeek' => array_values($countClientByDayThisWeek),
            'countClientByDayLastWeek' => array_values($countClientByDayLastWeek),
            'countClientByDayThisYear' => array_values($countClientByDayThisYear),
            'countClientByDayLastYear' => array_values($countClientByDayLastYear),

            'countProductsSoldThisWeekByDay' => array_values($countProductsSoldThisWeekByDay),
            'countProductsSoldLastWeekByDay' => array_values($countProductsSoldLastWeekByDay),
            'countProductsSoldThisYearByDay' => array_values($countProductsSoldThisYearByDay),
            'countProductsSoldLastYearByDay' => array_values($countProductsSoldLastYearByDay),

            //best order
            'bestOrderToday' => $bestOrderToday,
            'bestOrderYesterday' => $bestOrderYesterday,
            'bestOrderThisWeek' => $bestOrderThisWeek,
            'bestOrderLastWeek' => $bestOrderLastWeek,
            'bestOrderThisMonth' => $bestOrderThisMonth,
            'bestOrderLastMonth' => $bestOrderLastMonth,
            'bestOrderThisYear' => $bestOrderThisYear,
            'bestOrderLastYear' => $bestOrderLastYear,

            //date peremption
            'countProduitDatePeremption' => $countProduitDatePeremption,
        ]);

        return $this->render('admin/index.html.twig', $_data);

    }

    /**
     * @Route("admin/liste", name="app_admin_liste")
     */
    /*public function loadMenuContent(
        Request $request,
        UserPasswordHasherInterface $encoder
        )
    {
        $menu = $request->get('menu');

        $request->getSession()->set('menu', $menu);

        $listes = [];

        if($menu == "contact") {
            $listes = $this->contactRepo->findAll();
        } elseif($menu == "social") {
            $listes = $this->socialLinkRepo->findAll();
        } elseif ($menu == "produit") {
            $listes = $this->productRepository->findAll();
        } elseif ($menu == "categorie") {
            $listes = $this->categoryRepository->findAll();
        } elseif ($menu == "type") {
            $listes = $this->typeRepository->findAll();
        } elseif ($menu == "message") {
            $listes = $this->messageRepository->findAll();
        }

        if(!$menu) {
            return $this->render('admin/dashboard/index.html.twig');
        } elseif($menu == "profile") {
            $admin = $this->getUser();

            $form = $this->createForm(AdminType::class, $admin);
            
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                
                $this->em->persist($admin);
                $this->em->flush();

                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }

                $this->addFlash('success', 'Profile modifié avec succès');
                return $this->redirectToRoute('app_admin_liste');
            }

            $passwordUpdate = new PasswordUpdate();

            $formPwd = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

            $formPwd->handleRequest($request);

            $message = 'Votre mot de passe a bien été modifié! Vous pouvez maintenant vous connecter!';

            $messageError = 'Le mot de passe que vous avez tapé n\'est pas votre mot de passe actuel!';

            if ($formPwd->isSubmitted() && $formPwd->isValid()) {
                $admin = $this->getUser();

                $oldPassword = $form->get('oldPassword')->getData();

                $passwordValid = $encoder->isPasswordValid($admin, $oldPassword);
            
                if ($passwordValid) {
                    $newPassword = $passwordUpdate->getNewPassword();
                    $password = $encoder->hashPassword($admin, $newPassword);
            
                    $admin->setPassword($password);
            
                    $em->persist($admin);
                    $em->flush();
            
                    $this->addFlash(
                        'success',
                        $message
                    );

                    $tokenStorage->setToken(null);
            
                    return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
                } else {
                    $form->get('oldPassword')->addError(new FormError($messageError));
                }
            }
            $content = $this->renderView('admin/profile/index.html.twig', [
                'form' => $form->createView(),
                'menu' => $menu,
                'formPwd' => $formPwd->createView()
            ]);
            return new JsonResponse($content);

        }else {

            $content = $this->renderView('admin/liste/index.html.twig', [
                'menu' => $menu,
                'listes' => $listes,
            ]);

            return new JsonResponse($content);

        }

    }*/

    /**
     * @Route("/admin/liste/update-is-active/{id}", name="app_is_active")
     */
    /*public function updateIsActive(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $id = $request->get('id');
        $menu = $request->getSession()->get('menu');

        switch ($menu) {
            case 'contact':
                $entity = $this->contactRepo->findOneById($id);
                break;
            case 'social':
                $entity = $this->socialLinkRepo->findOneById($id);
                break;
            case 'produit':
                $entity = $this->productRepository->findOneById($id);
                break;
            case 'categorie':
                $entity = $this->categoryRepository->findOneById($id);
                break;
            case 'type':
                $entity = $this->typeRepository->findOneById($id);
                break;
            default:
                throw $this->createNotFoundException('Menu inconnu');
        }

        if (!$entity) {
            throw $this->createNotFoundException('Aucune entité trouvée pour l\'identifiant. '.$id);
        }

        $entity->setIsActive(!$entity->isIsActive());
        $em->persist($entity);
        $em->flush();

        // Renvoyer une réponse JSON avec l'état mis à jour
        return new JsonResponse(['isActive' => $entity->isIsActive()]);
    }*/

    
}
