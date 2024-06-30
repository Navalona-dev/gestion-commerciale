<?php

namespace App\Controller\Admin;

use App\Entity\ProduitType;
use App\Form\TypeProduitType;
use App\Service\AccesService;
use App\Service\ProduitTypeService;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/produit/type', name: 'types')]
class ProduitTypeController extends AbstractController
{
    private $accesService;
    public function __construct(AccesService $AccesService)
    {
        $this->accesService = $AccesService;
    }
    
    #[Route('/', name: '_liste')]
    public function index(ProduitTypeService $produitTypeService): Response
    {
        $data = [];
        try {
            
            $produitType = $produitTypeService->getAllProduitTypes();
            if ($produitType == false) {
                $produitType = [];
            }
           
            $data["html"] = $this->renderView('admin/produit_type/index.html.twig', [
                'listes' => $produitType,
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
      
    }

    #[Route('/new', name: '_create')]
    public function create(Request $request, ProduitTypeService $produitTypeService)
    {
       
        try {
            $produitType = new ProduitType();
            $form = $this->createForm(TypeProduitType::class, $produitType);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $produitTypeService->add($produitType);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                $this->addFlash('success', 'Création type de produit "' . $produitType->getNom() . '" avec succès.');
                return $this->redirectToRoute('types_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_type/new.html.twig', [
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

    #[Route('/edit/{produitType}', name: '_edit')]
    public function edit(Request $request, ProduitType $produitType, ProduitTypeService $produitTypeService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(TypeProduitType::class, $produitType, []);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $produitTypeService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification application "' . $categorie->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('applications_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_type/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $produitType->getId(),
            ]);
            return new JsonResponse($data);
        } catch (PropertyVideException $PropertyVideException) {
            $data['exception'] = $PropertyVideException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        } catch (UniqueConstraintViolationException $UniqueConstraintViolationException) {
            throw $this->createNotFoundException('Exception' . $UniqueConstraintViolationException->getMessage());
        } catch (MappingException $MappingException) {
            $data['exception'] = $MappingException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $MappingException->getMessage());
        } catch (ORMInvalidArgumentException $ORMInvalidArgumentException) {
            $data['exception'] = $ORMInvalidArgumentException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $ORMInvalidArgumentException->getMessage());
        } catch (UnsufficientPrivilegeException $UnsufficientPrivilegeException) {
            $data['exception'] = $UnsufficientPrivilegeException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $UnsufficientPrivilegeException->getMessage());
        }catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
            $data['exception'] = $NotNullConstraintViolationException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $NotNullConstraintViolationException->getMessage());
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/delete/{produitType}', name: '_delete')]
    public function delete(Request $request, ProduitType $produitType, ProduitTypeService $produitTypeService)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $produitTypeService->remove($produitType);
                $produitTypeService->update();
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
            }
                
        } catch (PropertyVideException $PropertyVideException) {
            $data['exception'] = $PropertyVideException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        } catch (UniqueConstraintViolationException $UniqueConstraintViolationException) {
            $data['exception'] = $UniqueConstraintViolationException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            throw $this->createNotFoundException('Exception' . $UniqueConstraintViolationException->getMessage());
        } catch (MappingException $MappingException) {
            $data['exception'] = $MappingException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $MappingException->getMessage());
        } catch (ORMInvalidArgumentException $ORMInvalidArgumentException) {
            $data['exception'] = $ORMInvalidArgumentException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $ORMInvalidArgumentException->getMessage());
        } catch (UnsufficientPrivilegeException $UnsufficientPrivilegeException) {
            $data['exception'] = $UnsufficientPrivilegeException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $UnsufficientPrivilegeException->getMessage());
        }catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
            $data['exception'] = $NotNullConstraintViolationException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $NotNullConstraintViolationException->getMessage());
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
    }


    #[Route("/update-is-active/{id}", name:"app_is_active_produit-type")]
    public function updateIsActive(Request $request, EntityManagerInterface $em, ProduitType $produitType): JsonResponse
    {
        if (!$produitType) {
            throw $this->createNotFoundException('Aucune entité trouvée pour l\'identifiant. '.$id);
        }

        $produitType->setIsActive(!$produitType->getIsActive());
        $em->persist($produitType);
        $em->flush();

        // Renvoyer une réponse JSON avec l'état mis à jour
        return new JsonResponse(['isActive' => $produitType->getIsActive()]);
    }
}
