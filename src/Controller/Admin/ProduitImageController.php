<?php

namespace App\Controller\Admin;

use App\Entity\ProductImage;
use App\Service\AccesService;
use App\Form\ProduitImageType;
use App\Entity\ProduitCategorie;
use App\Service\ProduitImageService;
use App\Exception\PropertyVideException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitCategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use App\Service\ApplicationManager;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/produit/image', name: 'produit_images')]
class ProduitImageController extends AbstractController
{
    private $accesService;
    private $produitImageService;
    private $application;

    public function __construct(AccesService $AccesService, ProduitImageService $produitImageService, ApplicationManager $applicationManager)
    {
        $this->accesService = $AccesService;
        $this->produitImageService = $produitImageService;
        $this->application = $applicationManager->getApplicationActive();
    }

    #[Route('/new', name: '_create')]
    public function create(Request $request, ProduitCategorieRepository $produitCategorieRepo)
    {
        $produitCategorieId = $request->getSession()->get('produitCategorieId');

        $produitCategorie = $produitCategorieRepo->findOneBy(['id' => $produitCategorieId]);

        try {
            $produitImage = new ProductImage();
            $form = $this->createForm(ProduitImageType::class, $produitImage);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $this->produitImageService->add($produitImage, $produitCategorie);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                //$this->addFlash('success', 'Création d\'une image "' . $produitImage->getNom() . '" avec succès.');
                //return $this->redirectToRoute('produit_images_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_image/new.html.twig', [
                'form' => $form->createView(),
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
    
    #[Route('/edit/{productImage}', name: '_edit')]
    public function edit(Request $request, ProductImage $productImage, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
       
        $data = [];
        try {
            
            $form = $this->createForm(ProduitImageType::class, $productImage);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $this->produitImageService->add($productImage, $productImage->getProduitCategorie(), true);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                //$this->addFlash('success', 'Création d\'une image "' . $productImage->getNom() . '" avec succès.');
                //return $this->redirectToRoute('produit_images_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_image/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $productImage->getId(),
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
        } catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
            $this->createNotFoundException('Exception' . $NotNullConstraintViolationException->getMessage());
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
           
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/{produitCategorie}', name: '_liste')]
    public function index(
        Request $request, 
        ProduitCategorie $produitCategorie,
        SessionInterface $session): Response
    {   
        $session->set('produitCategorieId', $produitCategorie->getId());

        $data = [];

        try {
            
            $stocks = $this->produitImageService->getImageByProduit($produitCategorie);
            if ($stocks == false) {
                $stocks = [];
            }
          
            $data["html"] = $this->renderView('admin/produit_image/index.html.twig', [
                'listes' => $stocks,
                'id' => $produitCategorie->getId(),
                'produitCategory' => $produitCategorie,
                'application' => $this->application
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }

        return new JsonResponse($data);
        
    }

    #[Route('/refresh/produit', name: '_refresh')]
    public function refresh(Request $request, SessionInterface $session, ProduitCategorieRepository $produitCategorieRepository)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $produitCategorieId = $request->getSession()->get('produitCategorieId');
        $produitCategorie = $produitCategorieRepository->find($produitCategorieId);
        
        $data = [];
        try {
            
            $produitImages = $this->produitImageService->getImageByProduit($produitCategorie);
            if ($produitImages == false) {
                $produitImages = [];
            }
          
            $data["html"] = $this->renderView('admin/produit_image/index.html.twig', [
                'listes' => $produitImages,
                'id' => $produitCategorie->getId(),
                'produitCategory' => $produitCategorie,
                'application' => $this->application
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }

        return new JsonResponse($data);
    }

    #[Route('/delete/{productImage}', name: '_delete')]
    public function delete(Request $request, ProductImage $productImage)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->produitImageService->remove($productImage);
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
            } 
                
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
        } catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
            $this->createNotFoundException('Exception' . $NotNullConstraintViolationException->getMessage());
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }
}
