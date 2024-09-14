<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Form\StockType;
use App\Helpers\Helpers;
use App\Service\AccesService;
use App\Service\StockService;
use App\Service\ProductService;
use App\Entity\ProduitCategorie;
use App\Service\ApplicationManager;
use App\Repository\ProductRepository;
use App\Exception\PropertyVideException;
use App\Service\ProduitCategorieService;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitCategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use App\Service\LogService;
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

    public function __construct(AccesService $AccesService, ApplicationManager $applicationManager, ProduitCategorieService $produitCategorieService, ProductService $productService, Helpers $helpers, LogService $logService)
    {
        $this->accesService = $AccesService;
        $this->produitCategorieService = $produitCategorieService;
        $this->productService = $productService;
        $this->application = $applicationManager->getApplicationActive();
        $this->helpers = $helpers;
        $this->logService = $logService;
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

                    $user = $this->getUser();
                    $data["produit"] = $produitCategorie->getNom();
                    $data["dateReception"] = (new \DateTime())->format("d-m-Y h:i:s");
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
                    $this->logService->addLog($request, "reception", $this->application->getId(), $produitCategorie->getReference(), $data);

                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                $this->addFlash('success', 'Création de stock avec succès.');
                return $this->redirectToRoute('stocks_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/stock/new.html.twig', [
                'form' => $form->createView(),
                'idProduit' => $produitCategorieId
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
        $oldQtt = $request->get('oldQtt');
        $totalStock = $request->get('totalStock');
        $quantity = $request->get('quantity');
        $qttVendu = $request->get('qttVendu');
        $produitCategorie = $stock->getProduitCategorie();
        $idProduit = $produitCategorie->getId();

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
                'qttVendu' => $qttVendu,
                'quantity' => $quantity,
                'idProduit' => $idProduit,
                'qttRestant' => ($produitCategorie->getStockRestant() != null ? $produitCategorie->getStockRestant() : 0)
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

        $data = [];
        
        try {
            
            $stocks = $stockService->getStockByProduit($produitCategorie);
            if ($stocks == false) {
                $stocks = [];
            }
            $qttVendu = $productRepo->countByAffairePaye('paye', 'commande', $produitCategorie->getReference());

            //$allQtt = $stockService->getQuantiteVenduByReferenceProduit($produitCategorie->getReference());
            $data["html"] = $this->renderView('admin/stock/index.html.twig', [
                'listes' => $stocks,
                'id' => $produitCategorie->getId(),
                'produitCategory' => $produitCategorie,
                //'qttVendu' => ($allQtt != false ? $allQtt['qttVendu'] : 0)
                'qttVendu' => $qttVendu
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
    
            foreach ($stocks as $stck) {
                $qttStock = $stck->getQtt();
                $totalQttStock += $qttStock;
            }
    
            foreach ($transferts as $transfert) {
                $qttTransfert = $transfert->getQuantity();
                $totalQttTransfert += $qttTransfert;
            }
    
            $totalQttStock = intVal($totalQttStock);
            $totalQttTransfert = intVal($totalQttTransfert);
    
            if ($request->isXmlHttpRequest()) {
                // Conditions de validation avant suppression
                if ($produitCategorie->getStockRestant() <= $stock->getQtt()) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le stock restant est inférieur ou égal à la quantité de stock à supprimer.'], Response::HTTP_OK);
                } elseif ($totalQttStock <= $totalQttTransfert) {
                    return new JsonResponse(['status' => 'error', 'message' => 'La quantité totale en stock est inférieure ou égale à la quantité totale transférée.'], Response::HTTP_OK);
                } elseif ($totalQttStock - $stock->getQtt() <= $totalQttTransfert) {
                    return new JsonResponse(['status' => 'error', 'message' => 'La quantité totale en stock moins la quantité de stock à supprimer est inférieure ou égale à la quantité totale transférée.'], Response::HTTP_OK);
                } else {
                    // Suppression du stock
                    $stockService->remove($stock, $produitCategorie);
                    return new JsonResponse(['status' => 'success', 'idProduit' => $idProduit], Response::HTTP_OK);
                }
            }
    
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
