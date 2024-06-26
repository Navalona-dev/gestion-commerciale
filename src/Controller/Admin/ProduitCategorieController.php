<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Service\AccesService;
use App\Entity\ProduitCategorie;
use App\Form\ProduitCategorieType;
use App\Exception\PropertyVideException;
use App\Service\ProduitCategorieService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/produit/categorie', name: 'produit_categories')]
class ProduitCategorieController extends AbstractController
{
    private $accesService;
    public function __construct(AccesService $AccesService)
    {
        $this->accesService = $AccesService;
    }
    
    #[Route('/', name: '_liste')]
    public function index(ProduitCategorieService $produitCategorieService): Response
    {
        $data = [];
        try {
            
            $produitCategories = $produitCategorieService->getAllProduitCategories();
            if ($produitCategories == false) {
                $produitCategories = [];
            }
           
            $data["html"] = $this->renderView('admin/produit_categorie/index.html.twig', [
                'listes' => $produitCategories,
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }

    #[Route('/new', name: '_create')]
    public function create(
    Request $request, 
    ProduitCategorieService $ProduitCategorieService)
    {
       
        try {
            $produitCategorie = new ProduitCategorie();
            $form = $this->createForm(ProduitCategorieType::class, $produitCategorie);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $ProduitCategorieService->add($produitCategorie);

                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
        
                $this->addFlash('success', 'Création produit categorie "' . $produitCategorie->getNom() . '" avec succès.');
                return $this->redirectToRoute('produit_categories_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_categorie/new.html.twig', [
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
}
