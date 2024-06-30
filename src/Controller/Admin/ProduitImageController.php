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
    public function __construct(AccesService $AccesService)
    {
        $this->accesService = $AccesService;
    }

    #[Route('/new', name: '_create')]
    public function create(Request $request, ProduitImageService $produitImageService, ProduitCategorieRepository $produitCategorieRepo)
    {
        $produitCategorieId = $request->getSession()->get('produitCategorieId');

        $produitCategorie = $produitCategorieRepo->findOneBy(['id' => $produitCategorieId]);

        try {
            $produitImage = new ProductImage();
            $form = $this->createForm(ProduitImageType::class, $produitImage);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $produitImageService->add($produitImage, $produitCategorie);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                $this->addFlash('success', 'Création d\'une image "' . $produitImage->getNom() . '" avec succès.');
                return $this->redirectToRoute('produit_images_liste');
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
    
    #[Route('/{produitCategorie}', name: '_liste')]
    public function index(Request $request, ProduitImageService $produitImageService, ProduitCategorie $produitCategorie): Response
    {   
        $request->getSession()->set('produitCategorieId', $produitCategorie->getId());

        $data = [];
        try {
            
            $stocks = $produitImageService->getImageByProduit($produitCategorie);
            if ($stocks == false) {
                $stocks = [];
            }
          
            $data["html"] = $this->renderView('admin/produit_image/index.html.twig', [
                'listes' => $stocks,
                'id' => $produitCategorie->getId(),
                'produitCategory' => $produitCategorie,
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }

        return new JsonResponse($data);
        
    }

    #[Route('/refresh/produit', name: '_refresh')]
    public function refresh(Request $request, ProduitImageService $produitImageService, SessionInterface $session, ProduitCategorieRepository $produitCategorieRepository)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $produitCategorieId = $request->getSession()->get('produitCategorieId');
        $produitCategorie = $produitCategorieRepository->find($produitCategorieId);
        $produitImages = $produitImageService->getImageByProduit($produitCategorie);

        if ($produitImages == false) {
            $produitImages = [];
        }
        
        $data = [];
        try {
            
            $produitImages = $produitImage->getImageByProduit($produitCategorie);
            if ($produitImages == false) {
                $produitImages = [];
            }
          
            $data["html"] = $this->renderView('admin/produit_image/index.html.twig', [
                'listes' => $produitImages,
                'id' => $produitCategorie->getId(),
                'produitCategory' => $produitCategorie,
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }

        return new JsonResponse($data);
    }
}
