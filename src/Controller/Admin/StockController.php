<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Form\StockType;
use App\Helpers\Helpers;
use App\Entity\Transfert;
use App\Form\TransfertType;
use App\Service\LogService;
use App\Service\AccesService;
use App\Service\StockService;
use App\Service\ProductService;
use App\Entity\ProduitCategorie;
use App\Repository\StockRepository;
use App\Service\ApplicationManager;
use App\Repository\ProductRepository;
use App\Exception\PropertyVideException;
use App\Service\ProduitCategorieService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitCategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/stock', name: 'stocks')]
class StockController extends AbstractController
{
    private $accesService;
    private $produitCategorieService;
    private $application;
    private $helpers;
    private $productService;
    private $logService;
    private $em;
    private $produitCategorieRepo;

    public function __construct(
        AccesService $AccesService, 
        ApplicationManager $applicationManager, 
        ProduitCategorieService $produitCategorieService, 
        ProductService $productService, 
        Helpers $helpers, 
        LogService $logService,
        EntityManagerInterface $em,
        ProduitCategorieRepository $produitCategorieRepo)
    {
        $this->accesService = $AccesService;
        $this->produitCategorieService = $produitCategorieService;
        $this->productService = $productService;
        $this->application = $applicationManager->getApplicationActive();
        $this->helpers = $helpers;
        $this->logService = $logService;
        $this->em = $em;
        $this->produitCategorieRepo = $produitCategorieRepo;
    }

    #[Route('/new', name: '_create')]
    public function create(Request $request, StockService $stockService, ProduitCategorieRepository $produitCategorieRepo)
    {
        $produitCategorieId = $request->getSession()->get('produitCategorieId');

        $produitCategorie = $produitCategorieRepo->findOneBy(['id' => $produitCategorieId]);

        try {
            $stock = new Stock();
            $form = $this->createForm(StockType::class, $stock);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                   $formData = $form->getData();
                   $datePeremption = $formData->getDatePeremption()->getDate();
                    
                    $stockService->add($stock, $produitCategorie, $datePeremption);

                  // Récupérer le stock restant
                    $stockRestant = $produitCategorie->getStockRestant();
                    $sousUnite = 0;

                    // Vérifiez si le stock restant est un entier ou contient des décimales
                    if ($stockRestant == floor($stockRestant)) {
                        // Si le stock est entier, on le prend tel quel
                        $stockRestantStr = (string)$stockRestant; // Convertir en chaîne pour la concaténation
                        $stockRestantStr .= " " . $produitCategorie->getPresentationGros();
                    } else {
                        // Calculer la partie entière et la partie décimale
                        $sacs = floor($stockRestant); // Prendre la partie entière
                        $decimalPart = $stockRestant - $sacs; // Calculer la partie décimale

                        // Calculer les sous-unités
                        $sousUnite = $decimalPart * $produitCategorie->getVolumeGros();
                        
                        // Créer la chaîne de stock restant
                        $stockRestantStr = $sacs . " " . $produitCategorie->getPresentationGros();

                        if ($sousUnite > 0) {
                            $stockRestantStr .= " et " . $sousUnite . " " . $produitCategorie->getUniteVenteGros();
                        }
                    }

                    //// End format stock
                    $user = $this->getUser();
                    $data["produit"] = $produitCategorie->getNom();
                    $data["dateReception"] = (new \DateTime())->format("d-m-Y h:i:s");
                    $data["dateTransfert"] = null;
                    $data["dateSortie"] = null;
                    $data["userDoAction"] = $user->getUserIdentifier();
                    $data["source"] = $this->application->getEntreprise();
                    $data["destination"] = $this->application->getEntreprise();
                    $data["action"] = "Ajout stock";
                    $data["type"] = "Ajout stock";
                    $data["qtt"] = $stock->getQtt() . ' ' . $produitCategorie->getPresentationGros();
                    $data["stockRestant"] = $stockRestantStr;
                    $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
                    $data["typeSource"] = "Point de vente";
                    $data["typeDestination"] = "Point de vente";
                    $data["commande"] = null;
                    $data["commandeId"] = null;
                    $data["sourceId"] =  $this->application->getId();
                    $data["destinationId"] = $this->application->getId();
                    $this->logService->addLog($request, "reception", $this->application->getId(), $produitCategorie->getReference(), $data);

                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                $this->addFlash('success', 'Création de stock avec succès.');
                return $this->redirectToRoute('stocks_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/stock/new.html.twig', [
                'form' => $form->createView(),
                'idProduit' => $produitCategorieId,
                'produitCategorie' => $produitCategorie
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

    #[Route('/edit/{stock}', name: '_edit')]
    public function edit(Request $request, StockService $stockService, Stock $stock, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $oldQtt = (integer) $request->get('oldQtt');
        $totalStock = $request->get('totalStock');
        $quantity = $request->get('quantity');
        $qttVendu = $request->get('qttVendu');
        $produitCategorie = $stock->getProduitCategorie();
        $idProduit = $produitCategorie->getId();

        //gerer le qtt  par sac et unité
        $qtt = $stock->getQtt();
        $sacs = floor($qtt);

        // Unité (partie décimale)
        $decimal = $qtt - $sacs;
        $unite = $decimal * $produitCategorie->getVolumeGros();
        $messageUnite = '';
        if($unite > 0) {
            $unite = number_format($unite,2,'.','');
            $messageUnite = ' et ' . $unite . ' ' . $produitCategorie->getUniteVenteGros();
        }
        $qttFinal = $sacs . ' ' . $produitCategorie->getPresentationGros() . $messageUnite;

        $session->set('stock', $stock->getId());
        $data = [];
        try {
            $form = $this->createForm(StockType::class, $stock, []);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $formData = $form->getData();
                    
                    $datePeremption = $formData->getDatePeremption()->getDate();

                    $qtt = $formData->getQtt();

                    $stockService->edit($stock, $produitCategorie, $oldQtt, $datePeremption, $qtt);
                    
                    $stockRestant = $produitCategorie->getStockRestant();
                    $sousUnite = 0;

                    // Vérifiez si le stock restant est un entier ou contient des décimales
                    if ($stockRestant == floor($stockRestant)) {
                        // Si le stock est entier, on le prend tel quel
                        $stockRestantStr = (string)$stockRestant; // Convertir en chaîne pour la concaténation
                        $stockRestantStr .= " " . $produitCategorie->getPresentationGros();
                    } else {
                        // Calculer la partie entière et la partie décimale
                        $sacs = floor($stockRestant); // Prendre la partie entière
                        $decimalPart = $stockRestant - $sacs; // Calculer la partie décimale

                        // Calculer les sous-unités
                        $sousUnite = $decimalPart * $produitCategorie->getVolumeGros();
                        
                        // Créer la chaîne de stock restant
                        $stockRestantStr = $sacs . " " . $produitCategorie->getPresentationGros();

                        if ($sousUnite > 0) {
                            $stockRestantStr .= " et " . $sousUnite . " " . $produitCategorie->getUniteVenteGros();
                        }
                    }

                    //// End format stock
                    $user = $this->getUser();
                    $data["produit"] = $produitCategorie->getNom();
                    $data["dateReception"] = (new \DateTime())->format("d-m-Y h:i:s");
                    $data["dateTransfert"] = null;
                    $data["dateSortie"] = null;
                    $data["userDoAction"] = $user->getUserIdentifier();
                    $data["source"] = $this->application->getEntreprise();
                    $data["destination"] = $this->application->getEntreprise();
                    $data["action"] = "Modification stock";
                    $data["type"] = "Modification stock";
                    $data["qtt"] = $stock->getQtt() . ' ' . $produitCategorie->getPresentationGros();
                    $data["stockRestant"] = $stockRestantStr;
                    $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
                    $data["typeSource"] = "Point de vente";
                    $data["typeDestination"] = "Point de vente";
                    $data["commande"] = null;
                    $data["commandeId"] = null;
                    $data["sourceId"] =  $this->application->getId();
                    $data["destinationId"] = $this->application->getId();
                    $this->logService->addLog($request, "reception", $this->application->getId(), $produitCategorie->getReference(), $data);


                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification application "' . $stock->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('applications_liste');
            }

            $data['exception'] = "";
            //$data['idProduit']= $idProduit;

            $data["html"] = $this->renderView('admin/stock/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $stock->getId(),
                'oldQtt' => $stock->getQtt(),
                'totalStock' => $totalStock,
                'produitCategorie' => $produitCategorie,
                'qttVendu' => $qttVendu,
                'quantity' => $quantity,
                'idProduit' => $idProduit,
                'qttRestant' => ($produitCategorie->getStockRestant() != null ? $produitCategorie->getStockRestant() : 0),
                'qttFinal' => $qttFinal,
                'sacs' => $sacs . $produitCategorie->getPresentationGros(),
                'unite' => $unite . $produitCategorie->getUniteVenteGros()
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
        StockService $stockService, 
        ProduitCategorie $produitCategorie,
        SessionInterface $session,
        ProductRepository $productRepo): Response
    {   
        $session->set('produitCategorieId', $produitCategorie->getId());

        $idProduit = $produitCategorie->getId();

        $volumeGros = $produitCategorie->getVolumeGros();

        $data = [];
        
        try {
            
            $stocks = $stockService->getStockByProduit($produitCategorie);
            if ($stocks == false) {
                $stocks = [];
            }
            $totalQtt = 0;
            $tabQtt = [];
            foreach($stocks as $stock) {
                $qtt = $stock->getQtt();
                $totalQtt += $qtt;
                $tabQtt[] = $qtt;
            }

            //dd($tabQtt);

            $paiement = ['paye', 'enecheance'];

            $products = $productRepo->getQttByProduitAndTypeVente($paiement, 'commande', $produitCategorie->getReference());
            
            $totalQttVendu = 0;

            // Calculer la quantité vendue pour chaque produit
            foreach ($products as $product) {
                $qtt = $product['qtt']; // Quantité du produit
                $typeVente = $product['typeVente']; // Type de vente

                // Appliquer la logique de calcul
                if ($typeVente === 'gros') {
                    $totalQttVendu += $qtt; // Vente en gros, on ajoute directement
                } elseif ($typeVente === 'detail') {
                    // Vente au détail, on divise par le volume de gros
                    if ($volumeGros > 0) {
                        $totalQttVendu += $qtt / $volumeGros;
                    }
                }
            }

            //$allQtt = $stockService->getQuantiteVenduByReferenceProduit($produitCategorie->getReference());
            $data["html"] = $this->renderView('admin/stock/index.html.twig', [
                'listes' => $stocks,
                'id' => $produitCategorie->getId(),
                'produitCategory' => $produitCategorie,
                //'qttVendu' => ($allQtt != false ? $allQtt['qttVendu'] : 0)
                'qttVendu' => $totalQttVendu,
                'totalQtt' => $totalQtt
            ]);

            $data['idProduit'];

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }

        return new JsonResponse($data);
        
    }
    
    #[Route('/refresh/produit', name: '_refresh')]
    public function refresh(Request $request, StockService $stockService, SessionInterface $session, ProduitCategorieRepository $produitCategorieRepository)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $produitCategorieId = $request->getSession()->get('produitCategorieId');
        $produitCategorie = $produitCategorieRepository->find($produitCategorieId);
        $stocks = $stockService->getStockByProduit($produitCategorie);

        if ($stocks == false) {
            $stocks = [];
        }
        
        $data = [];
        try {
            
            $stocks = $stockService->getStockByProduit($produitCategorie);
            if ($stocks == false) {
                $stocks = [];
            }
          
            $data["html"] = $this->renderView('admin/stock/index.html.twig', [
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

    #[Route('/delete/{stock}', name: '_delete')]
    public function delete(Request $request, StockService $stockService, Stock $stock)
    {
        $produitCategorie = $stock->getProduitCategorie();

        $idProduit = $stock->getProduitCategorie()->getId();

        try {
            $totalQttTransfert = 0;
            $totalQttStock = 0;
    
            $transferts = $produitCategorie->getTransferts();
            $stocks = $produitCategorie->getStocks();
    
            foreach ($stocks as $stk) {
                $totalQttStock += $stk->getQtt();
            }
            
            foreach ($transferts as $transfert) {
                $totalQttTransfert += $transfert->getQuantity();
            }
    
            //$totalQttStock = intVal($totalQttStock);
            //$totalQttTransfert = intVal($totalQttTransfert);

            if ($request->isXmlHttpRequest()) {
                // Conditions de validation avant suppression
                if ($produitCategorie->getStockRestant() <= $stock->getQtt()) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le stock restant est inférieur ou égal à la quantité de stock à supprimer.'], Response::HTTP_OK);
                } elseif ($totalQttStock <= $totalQttTransfert) {
                    return new JsonResponse(['status' => 'error', 'message' => 'La quantité totale en stock est inférieure ou égale à la quantité totale transférée.'], Response::HTTP_OK);
                } elseif ($totalQttStock - $stock->getQtt() <= $totalQttTransfert) {
                    return new JsonResponse(['status' => 'error', 'message' => 'La quantité totale en stock moins la quantité de stock à supprimer est inférieure ou égale à la quantité totale transférée.'], Response::HTTP_OK);
                } else {
                    

                    //// End format stock
                    $user = $this->getUser();
                    $data["produit"] = $produitCategorie->getNom();
                    $data["dateReception"] = (new \DateTime())->format("d-m-Y h:i:s");
                    $data["dateTransfert"] = null;
                    $data["dateSortie"] = null;
                    $data["userDoAction"] = $user->getUserIdentifier();
                    $data["source"] = $this->application->getEntreprise();
                    $data["destination"] = $this->application->getEntreprise();
                    $data["action"] = "Suppression stock";
                    $data["type"] = "Suppression stock";
                    $data["qtt"] = $stock->getQtt() . ' ' . $produitCategorie->getPresentationGros();
                    $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
                    $data["typeSource"] = "Point de vente";
                    $data["typeDestination"] = "Point de vente";
                    $data["commande"] = null;
                    $data["commandeId"] = null;
                    $data["sourceId"] =  $this->application->getId();
                    $data["destinationId"] = $this->application->getId();

                    $stockService->remove($stock, $produitCategorie);

                    // Suppression du stock
                    $stockRestant = $produitCategorie->getStockRestant();
                    $sousUnite = 0;

                    // Vérifiez si le stock restant est un entier ou contient des décimales
                    if ($stockRestant == floor($stockRestant)) {
                        // Si le stock est entier, on le prend tel quel
                        $stockRestantStr = (string)$stockRestant; // Convertir en chaîne pour la concaténation
                        $stockRestantStr .= " " . $produitCategorie->getPresentationGros();
                    } else {
                        // Calculer la partie entière et la partie décimale
                        $sacs = floor($stockRestant); // Prendre la partie entière
                        $decimalPart = $stockRestant - $sacs; // Calculer la partie décimale

                        // Calculer les sous-unités
                        $sousUnite = $decimalPart * $produitCategorie->getVolumeGros();
                        
                        // Créer la chaîne de stock restant
                        $stockRestantStr = $sacs . " " . $produitCategorie->getPresentationGros();

                        if ($sousUnite > 0) {
                            $stockRestantStr .= " et " . $sousUnite . " " . $produitCategorie->getUniteVenteGros();
                        }
                    }

                    $data["stockRestant"] = $stockRestantStr;

                    $this->logService->addLog($request, "reception", $this->application->getId(), $produitCategorie->getReference(), $data);

                    return new JsonResponse(['status' => 'success', 'idProduit' => $idProduit], Response::HTTP_OK);
                }

                
            }
    
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/transfert/{stock}', name: '_transfert')]
    public function transfert(
        Stock $stock,
        Request $request, 
        StockRepository $stockRepository,
        EntityManagerInterface $em,
        StockService $stockService) {

        $data = [];
        try {

            $oldProduitCategorie = $stock->getProduitCategorie();

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
                $datePeremption = null;
                if($stock->getDatePeremption() != null) {
                    $datePeremption = $stock->getDatePeremption()->getDate();
                }

                $produitCategories = $this->produitCategorieRepo->findBy(['application' => $newApplication]);

                // Condition pour vérifier si $produitReference existe dans $produitCategories
                $productReferenceExists = null;
                foreach ($produitCategories as $produit) {
                    if ($produit->getReference() === $produitReference) {
                        $productReferenceExists = $produit;
                        break;
                    }
                }

                //ajout transfert
                if($stock->getQttRestant() < $quantity) {
                    return new JsonResponse(['status' => 'error', Response::HTTP_OK]);
                } else {
                    //$stockService->addTransfert($oldProduitCategorie, $newApplication, $quantity, $stock);
                    // Mise à jour du stock restant de l'ancienne catégorie de produit
                    
                    //$stockService->updateStockRestant($oldProduitCategorie, $quantity, $this->application);

                    if ($request->isXmlHttpRequest()) {

                    $stockService->addNewProductForNewApplication($productReferenceExists, $oldProduitCategorie, $quantity, $newApplication, $isChangePrice, $stock, $datePeremption, $this->application);
                        
                    $stockRestant = $oldProduitCategorie->getStockRestant();
                        $sacsStock = floor($stockRestant);

                        // Unité (partie décimale)
                        $decimalStock = $stockRestant - $sacsStock;
                        $uniteStock = $decimalStock * $oldProduitCategorie->getVolumeGros();
                        $messageUnite = '';
                        if($uniteStock > 0) {
                            $uniteStock = number_format($uniteStock,2,'.','');
                            $messageUnite = ' et ' . $uniteStock . ' ' . $oldProduitCategorie->getUniteVenteGros();
                        }
                        $stockRestantFinal = $sacsStock . ' ' . $oldProduitCategorie->getPresentationGros() . $messageUnite;

                    $user = $this->getUser();
                        $data["produit"] = $oldProduitCategorie->getNom();
                        $data["dateReception"] = null;
                        $data["dateTransfert"] = (new \DateTime())->format("d-m-Y h:i:s");
                        $data["dateSortie"] = (new \DateTime())->format("d-m-Y h:i:s");
                        $data["userDoAction"] = $user->getUserIdentifier();
                        $data["source"] = $this->application->getEntreprise();
                        $data["destination"] = $newApplication->getEntreprise();
                        $data["action"] = "transfert";
                        $data["type"] = "transfert";
                        $data["qtt"] = $quantity . $oldProduitCategorie->getPresentationGros();
                        $data["stockRestant"] = $stockRestantFinal;
                        $data["fournisseur"] = $oldProduitCategorie->getReference();
                        $data["typeSource"] = "Point de vente";
                        $data["typeDestination"] = "Point de vente";
                        $data["commande"] = null;
                        $data["commandeId"] = null;
                        $data["sourceId"] =  $this->application->getId();
                        $data["destinationId"] = $newApplication->getId();
                        $this->logService->addLog($request, "transfert", $this->application->getId(), $oldProduitCategorie->getReference(), $data);
                    
                        $this->em->flush();
                        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);

                    }
                }
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/stock/modal_transfert.html.twig', [
                'form' => $form->createView(),
                'applicationName' => $oldApplicationName,
                'produitCategorie' => $oldProduitCategorie,
                'stock' => $stock,

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