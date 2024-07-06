<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Form\TransfertType;
use App\Service\AccesService;
use App\Entity\ProduitCategorie;
use App\Form\ProduitCategorieType;
use App\Service\ApplicationManager;
use App\Exception\PropertyVideException;
use App\Form\UpdateProduitCategorieType;
use App\Service\ProduitCategorieService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitCategorieRepository;
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
    private $produitCategorieService;
    private $application;
    public function __construct(AccesService $AccesService, ApplicationManager $applicationManager, ProduitCategorieService $produitCategorieService)
    {
        $this->accesService = $AccesService;
        $this->produitCategorieService = $produitCategorieService;
        $this->application = $applicationManager->getApplicationActive();
    }
    
    #[Route('/', name: '_liste')]
    public function index(Request $request): Response
    {
        $request->getSession()->remove('produitCategorieId');

        $data = [];
        try {
            
            $produitCategories = $this->produitCategorieService->getAllProduitCategories();
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
    Request $request)
    {
       
        try {
            $produitCategorie = new ProduitCategorie();
            $form = $this->createForm(ProduitCategorieType::class, $produitCategorie);
            $fournisseurs = $this->produitCategorieService->getAllFournisseur();
            
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    
                    $idFournisseur = $request->get("produit_categorie_compte");
                    if(isset($idFournisseur) && !empty($idFournisseur)) {
                        $fournisseur = $this->produitCategorieService->getFournisseurById($idFournisseur);
                        if ($fournisseur) {
                            $produitCategorie->addCompte($fournisseur);
                            $fournisseur->addProduitCategory($produitCategorie);
                            $this->produitCategorieService->persist($fournisseur);
                        }
                    }
                    $produitCategorie->setApplication($this->application);
                    $this->produitCategorieService->add($produitCategorie);

                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
        
                $this->addFlash('success', 'Création produit categorie "' . $produitCategorie->getNom() . '" avec succès.');
                return $this->redirectToRoute('produit_categories_liste');
            } 

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_categorie/new.html.twig', [
                'form' => $form->createView(),
                'fournisseurs' => $fournisseurs
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

    #[Route('/{produitCategorie}', name: '_edit')]
    public function edit(Request $request, ProduitCategorie $produitCategorie)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(UpdateProduitCategorieType::class, $produitCategorie, []);
            $fournisseurs = $this->produitCategorieService->getAllFournisseur();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $idFournisseur = $request->get("produit_categorie_compte");
                    if(isset($idFournisseur) && !empty($idFournisseur)) {
                        $tabIdComptes = [];
                        if (count($produitCategorie->getComptes()) > 0) {
                            foreach ($produitCategorie->getComptes() as $key => $compte) {
                                if (!in_array($compte->getId(), $tabIdComptes)) {
                                    array_push($tabIdComptes,$compte->getId() );
                                }
                                $produitCategorie->removeCompte($compte);
                            }
                        }
                  
                        if (!in_array(( integer)$idFournisseur, $tabIdComptes)) {
                            
                            $fournisseur = $this->produitCategorieService->getFournisseurById($idFournisseur);
                        
                            if ($fournisseur) {
                                $produitCategorie->addCompte($fournisseur);
                                $fournisseur->addProduitCategory($produitCategorie);
                                $this->produitCategorieService->persist($fournisseur);
                            }
                        }
                        
                    }
                    $this->produitCategorieService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification application "' . $categorie->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('applications_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_categorie/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $produitCategorie->getId(),
                'fournisseurs' => $fournisseurs,
                'fournisseurId' => (null != $produitCategorie->getComptes()[0] ? $produitCategorie->getComptes()[0]->getId(): "0")
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

    #[Route('/delete/{produitCategorie}', name: '_delete')]
    public function delete(Request $request, ProduitCategorie $produitCategorie)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->produitCategorieService->remove($produitCategorie);
                $this->produitCategorieService->update();
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

    #[Route('/transfert/{produitCategorie}', name: '_transfert')]
    public function transfert(
        $produitCategorie,
        Request $request, 
        ProduitCategorieRepository $produitCategorieRepo,
        EntityManagerInterface $em,
        ProduitCategorieService $produitCategorieService) {

        $data = [];
        try {
            $oldProduitCategorie = $produitCategorieRepo->findOneBy(['id' => $produitCategorie]);
            $produitReference = $oldProduitCategorie->getReference();

            $form = $this->createForm(TransfertType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $date = new \DateTime();

                $formData = $form->getData();
                $newApplication = $formData->getApplication();
                $quantity = $formData->getQuantity();

                $produits = $produitCategorieRepo->findBy(['application' => $newApplication]);

                // Condition pour vérifier si $produitReference existe dans $produits
                $productReferenceExists = null;
                foreach ($produits as $produit) {
                    if ($produit->getReference() === $produitReference) {
                        $productReferenceExists = $produit;
                        break;
                    }
                }

                //ajout transfert
                if($oldProduitCategorie->getStockRestant() <= $quantity) {
                    return new JsonResponse(['status' => 'error', Response::HTTP_OK]);
                    
                } else {
                    $produitCategorieService->addTransfert($oldProduitCategorie, $newApplication, $quantity);
                }

                // Mise à jour du stock restant de l'ancienne catégorie de produit
                
                $produitCategorieService->updateStockRestant($oldProduitCategorie, $quantity);

                if ($request->isXmlHttpRequest()) {
                    if ($productReferenceExists) {
                        // Mise à jour du stock pour l'application cible si le produit existe
                        $produitCategorieService->updateStockNewApplication($productReferenceExists, $quantity);
                        
                    } else {
                        // Création d'une nouvelle catégorie de produit pour l'application cible
                        $produitCategorieService->addNewProductForNewApplication($oldProduitCategorie, $quantity, $newApplication);
                    }

                    $produitCategorieService->update();

                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_categorie/modal_transfert.html.twig', [
                'form' => $form->createView(),
                'id' => $oldProduitCategorie->getId(),
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
        } catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
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

}
