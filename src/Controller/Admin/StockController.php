<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Form\StockType;
use App\Service\StockService;
use App\Entity\ProduitCategorie;
use App\Exception\PropertyVideException;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitCategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use App\Helpers\Helpers;
use App\Service\AccesService;
use App\Service\ApplicationManager;
use App\Service\ProductService;
use App\Service\ProduitCategorieService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/admin/stock', name: 'stocks')]
class StockController extends AbstractController
{
    private $accesService;
    private $produitCategorieService;
    private $application;
    private $helpers;
    private $productService;

    public function __construct(AccesService $AccesService, ApplicationManager $applicationManager, ProduitCategorieService $produitCategorieService, ProductService $productService, Helpers $helpers)
    {
        $this->accesService = $AccesService;
        $this->produitCategorieService = $produitCategorieService;
        $this->productService = $productService;
        $this->application = $applicationManager->getApplicationActive();
        $this->helpers = $helpers;
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
                    $stockService->add($stock, $produitCategorie);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
        
                $this->addFlash('success', 'Création de stock "' . $stock->getNom() . '" avec succès.');
                return $this->redirectToRoute('stocks_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/stock/new.html.twig', [
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
    public function index(Request $request, StockService $stockService, ProduitCategorie $produitCategorie): Response
    {   
        $request->getSession()->set('produitCategorieId', $produitCategorie->getId());

        $data = [];
        try {
            
            $stocks = $stockService->getStockByProduit($produitCategorie);
            if ($stocks == false) {
                $stocks = [];
            }
            $allQtt = $stockService->getQuantiteVenduByReferenceProduit($produitCategorie->getReference());
           
            $data["html"] = $this->renderView('admin/stock/index.html.twig', [
                'listes' => $stocks,
                'id' => $produitCategorie->getId(),
                'produitCategory' => $produitCategorie,
                'qttVendu' => ($allQtt != false ? $allQtt['qttVendu'] : 0)
            ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
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
        $produitCategorie = $stock->getProduitCategorie();
        $session->set('stock', $stock->getId());
        $data = [];
        try {
            $form = $this->createForm(StockType::class, $stock, []);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $stockService->edit($stock, $produitCategorie, $oldQtt);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification application "' . $stock->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('applications_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/stock/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $stock->getId(),
                'oldQtt' => $stock->getQtt()
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
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
            }
    
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
