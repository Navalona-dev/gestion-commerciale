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
        $headerData = $headerDataProvider->getHeaderData();
        $idAffaire = $session->get('idAffaire');
        $idCompte = $session->get('idCompte');
        $idProduit = $session->get('produitCategorieId');

        $countAffaireToday = $this->dashboardService->getCountAffairesToday('paye', 'commande');
        $countAffaireYesterday = $this->dashboardService->getCountAffairesYesterday('paye', 'commande');
        $countAffaireThisWeek = $this->dashboardService->getCountAffairesThisWeek('paye', 'commande');

        if ($request->isXmlHttpRequest()) {
            try {

                $_data = array_merge($headerData, [
                    'listes' => [],
                    'countAffaireToday' => $countAffaireToday,
                    'countAffaireYesterday' => $countAffaireYesterday,
                    'countAffaireThisWeek' => $countAffaireThisWeek
                ]);
                
                $data["html"] = $this->renderView('admin/dashboard/index.html.twig', $_data);
            
                return new JsonResponse($data);
            } catch (\Exception $Exception) {
                $this->createNotFoundException('Exception' . $Exception->getMessage());
            }
            return new JsonResponse($data);
        } 

        //dd($idAffaire);

        $_data = array_merge($headerData, [
            'listes' => [],
            'idAffaire' => $idAffaire,
            'idCompte' => $idCompte,
            'idProduit' => $idProduit
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
