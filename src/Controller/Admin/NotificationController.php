<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Form\StockType;
use App\Entity\Notification;
use App\Service\StockService;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/notification', name: 'notifications')]
class NotificationController extends AbstractController
{
    #[Route('/', name: '_liste')]
    public function index(NotificationRepository $notificationRepo): Response
    {
        $data = [];
        try {
            
            $notifications = $notificationRepo->findByIsViewFalseOrNull();
            if ($notifications == false) {
                $notifications = [];
            }
           
            $data["html"] = $this->renderView('admin/notification/index.html.twig', [
                'listes' => $notifications,
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }

    #[Route('/new/stock/{id}', name: '_create')]
    public function create(
        Request $request, 
        StockService $stockService, 
        Notification $notification, 
        EntityManagerInterface $em)
    {
        $produitCategorie = $notification->getProduitCategorie();
        $request->getSession()->set('produitCategorieId', $produitCategorie->getId());

        try {
            $stock = new Stock();
            $form = $this->createForm(StockType::class, $stock);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $stockService->add($stock, $produitCategorie);

                    $notification->setIsView(true);
                    $em->persist($notification);
                    $em->flush();

                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                //$this->addFlash('success', 'Création de stock avec succès.');
                //return $this->redirectToRoute('stocks_liste', ['produitCategorie' => $produitCategorie]);
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/notification/new_stock.html.twig', [
                'form' => $form->createView(),
                'notification' => $notification
            ]);
           
            return new JsonResponse($data);
        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        } catch (UniqueConstraintViolationException $UniqueConstraintViolationException) {
            throw $this->createNotFoundException('Exception' . $UniqueConstraintViolationException->getMessage());
        } catch (MappingException $MappingException) {
            $this->createNotFoundException('Exception' . $MappingException->getMessage());
        } catch (ORMInvalidArgumentException $ORMInvalidArgumentException) {
            $this->createNotFoundException('Exception' . $ORMInvalidArgumentException->getMessage());
        } catch (UnsufficientPrivilegeException $UnsufficientPrivilegeException) {
            $this->createNotFoundException('Exception' . $UnsufficientPrivilegeException->getMessage());
        }catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
            $this->createNotFoundException('Exception' . $NotNullConstraintViolationException->getMessage());
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/update-is-view/{id}', name: '_update_is_view')]
    public function updateIsView(
        Request $request, 
        StockService $stockService, 
        Notification $notification, 
        EntityManagerInterface $em)
    {
        try {

            if ($request->isXmlHttpRequest()) {
                $notification->setIsView(true);
                $em->persist($notification);
                $em->flush();

                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
            } 
            
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
    }
}
