<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Form\TransfertType;
use Psr\Log\LoggerInterface;
use App\Service\AccesService;
use App\Entity\ProduitCategorie;
use App\Form\ProduitCategorieType;
use App\Service\ApplicationManager;
use App\Form\UpdatePriceProductType;
use App\Repository\ProductRepository;
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
use App\Service\LogService;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/produit/categorie', name: 'produit_categories')]
class ProduitCategorieController extends AbstractController
{
    private $accesService;
    private $produitCategorieService;
    private $application;
    private $logger;
    private $logService;

    public function __construct(
        AccesService $AccesService, 
        ApplicationManager $applicationManager, 
        ProduitCategorieService $produitCategorieService,
        LoggerInterface $productLogger,
        LogService $logService
        )
    {
        $this->accesService = $AccesService;
        $this->produitCategorieService = $produitCategorieService;
        $this->application = $applicationManager->getApplicationActive();
        $this->logger = $productLogger;
        $this->logService = $logService;

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

    #[Route('/date/peremption/proche', name: '_liste_peremption')]
    public function datePeremption(Request $request): Response
    {
        $request->getSession()->remove('produitCategorieId');

        $data = [];
        try {
            
            $produitCategories = $this->produitCategorieService->getAllProduitDatePeremption();
            if ($produitCategories == false) {
                $produitCategories = [];
            }
          
            $data["html"] = $this->renderView('admin/produit_categorie/date_peremption.html.twig', [
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
                    $fournisseur = null;
                    if(isset($idFournisseur) && !empty($idFournisseur)) {
                        $fournisseur = $this->produitCategorieService->getFournisseurById($idFournisseur);
                        if ($fournisseur) {
                            $produitCategorie->addCompte($fournisseur);
                            $fournisseur->addProduitCategory($produitCategorie);
                            $this->produitCategorieService->persist($fournisseur);
                        }
                    }
                    $reference = null;
                    if ($fournisseur != false && $fournisseur != null) {
                        $reference = $fournisseur->getCode();
                        $produitCategorie->setReference($reference.$produitCategorie->getReference());
                    }
                    
                    $produitCategorie->setQtt($produitCategorie->getStockRestant());
                    $produitCategorie->setApplication($this->application);
                    $this->produitCategorieService->add($produitCategorie);

                    $user = $this->getUser();
                    $data["produit"] = $produitCategorie->getNom();
                    $data["dateReception"] = (new \DateTime())->format("d-m-y h:i:s");
                    $data["dateTransfert"] = null;
                    $data["dateSortie"] = null;
                    $data["userDoAction"] = $user->getUserIdentifier();
                    $data["source"] = $this->application->getEntreprise();
                    $data["destination"] = $this->application->getEntreprise();
                    $data["action"] = "Ajout";
                    $data["type"] = "Ajout";
                    $data["qtt"] = $produitCategorie->getQtt();
                    $data["stockRestant"] = $produitCategorie->getStockRestant();
                    $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : $reference);
                    $data["typeSource"] = "Point de vente";
                    $data["typeDestination"] = "Point de vente";;
                    $data["commande"] = null;
                    $data["commandeId"] = null;
                    $data["sourceId"] =  $this->application->getId();
                    $data["destinationId"] = $this->application->getId();
                    $this->logService->addLog($request, "transfert", $this->application->getId(), $produitCategorie->getReference(), $data);

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

    #[Route('/inventaire/{produitCategorie}', name: '_inventaire')]
    public function inventaire(Request $request, ProduitCategorie $produitCategorie)
    {
        $data = [];
        try {
            $logFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/historique/';
            $prefix = 'log_'.$this->application->getId().'_'.$produitCategorie->getReference();
           
            $parsedLines = $this->logService->getContentLog($logFilePath, $prefix);
              //dd($parsedLines, $prefix, $produitCategorie);
            $htmlContent = $this->renderView('admin/produit_categorie/inventaire.html.twig', [
                'logLines' => $parsedLines,
                'logEmpty' => false
            ]);
    
            $data["html"] = $htmlContent;
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
                    // Obtenir l'utilisateur connecté
                    $user = $this->getUser();

                    // Créer log
                    $this->logger->info('Produit catégorie modifié', [
                        'Produit' => $produitCategorie->getNom(),
                        'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                        'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                        'ID Application' => $this->application->getId()
                    ]);

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

            $oldApplication = $oldProduitCategorie->getApplication();

            $oldApplicationName = $oldApplication->getEntreprise();

            $form = $this->createForm(TransfertType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $date = new \DateTime();

                $isChangePrice = $request->get('is_change_price');

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

                    $produitCategorieService->addNewProductForNewApplication($productReferenceExists, $oldProduitCategorie, $quantity, $newApplication, $isChangePrice);

                    $user = $this->getUser();
                    $data["produit"] = $oldProduitCategorie->getNom();
                    $data["dateReception"] = null;
                    $data["dateTransfert"] = (new \DateTime())->format("d-m-y h:i:s");
                    $data["dateSortie"] = (new \DateTime())->format("d-m-y h:i:s");
                    $data["userDoAction"] = $user->getUserIdentifier();
                    $data["source"] = $this->application->getEntreprise();
                    $data["destination"] = $newApplication->getEntreprise();
                    $data["action"] = "transfert";
                    $data["type"] = "transfert";
                    $data["qtt"] = $quantity;
                    $data["stockRestant"] = $oldProduitCategorie->getStockRestant();
                    $data["fournisseur"] = $oldProduitCategorie->getReference();
                    $data["typeSource"] = "Point de vente";
                    $data["typeDestination"] = "Point de vente";;
                    $data["commande"] = null;
                    $data["commandeId"] = null;
                    $data["sourceId"] =  $this->application->getId();
                    $data["destinationId"] = $newApplication->getId();;
                    $this->logService->addLog($request, "transfert", $this->application->getId(), $oldProduitCategorie->getReference(), $data);
            
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/produit_categorie/modal_transfert.html.twig', [
                'form' => $form->createView(),
                'id' => $oldProduitCategorie->getId(),
                'applicationName' => $oldApplicationName
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

    #[Route('/edit/prix/{produitCategorie}', name: '_edit_price')]
    public function updatePrice(Request $request, ProduitCategorie $produitCategorie, EntityManagerInterface $em)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(UpdatePriceProductType::class, $produitCategorie, []);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $notifications = $produitCategorie->getNotifications();
                    foreach($notifications as $notification) {
                        $notification->setIsView(true);
                        $em->persist($notification);
                    }
                    $produitCategorie->setIsChangePrix(false);

                    // Obtenir l'utilisateur connecté
                    $user = $this->getUser();

                    // Créer log
                    $this->logger->info('Prix de produit catégorie modifié', [
                        'Produit' => $produitCategorie->getNom(),
                        'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                        'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail'
                    ]);

                    $this->produitCategorieService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/transfert/modal_update_price.html.twig', [
                'form' => $form->createView(),
                'id' => $produitCategorie->getId(),
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

    #[Route('/quantite/vendu/{produitCategorie}', name: '_qtt_vendu')]
    public function qttVendu(
        Request $request, 
        ProductRepository $productRepo,
        ProduitCategorie $produitCategorie): Response
    {
        $request->getSession()->set('produitCategorieId', $produitCategorie->getId());

        $data = [];
        try {
            
            $referenceProduitCategorie = $produitCategorie->getReference();

            $products = $productRepo->findByAffairePaye('paye', 'commande', $referenceProduitCategorie);
            $countQtt = $productRepo->countByAffairePaye('paye', 'commande', $referenceProduitCategorie);
            
            if ($products == false) {
                $products = [];
            }
          
            $data["html"] = $this->renderView('admin/produit_categorie/qtt_vendu.html.twig', [
                'listes' => $products,
                'countQtt' => $countQtt
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }

}
