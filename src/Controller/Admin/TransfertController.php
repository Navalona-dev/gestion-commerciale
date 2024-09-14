<?php

namespace App\Controller\Admin;

use App\Entity\Transfert;
use App\Service\AccesService;
use App\Service\ApplicationManager;
use App\Repository\TransfertRepository;
use App\Repository\ProduitCategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/transfert', name: 'transferts')]
class TransfertController extends AbstractController
{
    private $accesService;
    private $application;
    private $transfertRepo;
    private $produitCategorieRepo;
    public function __construct(
        AccesService $AccesService, 
        ApplicationManager $applicationManager,
        TransfertRepository $transfertRepo,
        ProduitCategorieRepository $produitCategorieRepo)
    {
        $this->accesService = $AccesService;
        $this->application = $applicationManager->getApplicationActive();
        $this->transfertRepo = $transfertRepo;
        $this->produitCategorieRepo = $produitCategorieRepo;
    }

    #[Route('/{produitCategory}', name: '_listes')]
    public function index($produitCategory): Response
    {
        $data = [];
        $produitCategorie = $this->produitCategorieRepo->findOneBy(['id' => $produitCategory]);
        try {
            
            $transferts = $this->transfertRepo->findByProduit($produitCategorie) ;
            
          
            $data["html"] = $this->renderView('admin/transfert/index.html.twig', [
                'listes' => $transferts,
                'produitCategory' => $produitCategorie
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }

    #[Route('/refresh/produit', name: '_refresh')]
    public function refresh(): Response
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $produitCategorieId = $request->getSession()->get('produitCategorieId');
        $produitCategorie = $produitCategorieRepository->find($produitCategorieId);
        $transferts = $this->transfertRepo->findByProduit($produitCategorie);

        if ($transferts == false) {
            $transferts = [];
        }
        
        $data = [];
        try {
            
            $transferts = $this->transfertRepo->findByProduit($produitCategorie);
            if ($transferts == false) {
                $transferts = [];
            }
          
            $data["html"] = $this->renderView('admin/transfert/index.html.twig', [
                'listes' => $transferts,
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

    #[Route('/annule/{transfert}', name: '_annule')]
    public function annule(Transfert $transfert): Response
    {
        $data = [];
        $produitCategorie = $transfert->getProduitCategorie();
        try {

            $qtt = $transfert->getQtt();
            
            return new JsonResponse();
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }

}
