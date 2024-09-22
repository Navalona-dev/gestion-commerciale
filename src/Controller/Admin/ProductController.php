<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use App\Entity\Product;
use App\Helpers\Helpers;
use App\Form\TransfertType;
use Psr\Log\LoggerInterface;
use App\Service\AccesService;
use App\Service\AffaireService;
use App\Service\ProductService;
use App\Entity\ProduitCategorie;
use App\Form\ProduitCategorieType;
use App\Service\ApplicationManager;
use App\Form\UpdatePriceProductType;
use App\Repository\AffaireRepository;
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
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/product', name: 'product')]
class ProductController extends AbstractController
{
    private $accesService;
    private $produitCategorieService;
    private $application;
    private $helpers;
    private $productService;
    private $affaireService;
    private $logger;

    public function __construct(
        AccesService $AccesService, 
        ApplicationManager $applicationManager, 
        ProduitCategorieService $produitCategorieService, 
        ProductService $productService, 
        Helpers $helpers, 
        AffaireService $affaireService,
        LoggerInterface $affaireLogger
        )
    {
        $this->accesService = $AccesService;
        $this->produitCategorieService = $produitCategorieService;
        $this->productService = $productService;
        $this->application = $applicationManager->getApplicationActive();
        $this->helpers = $helpers;
        $this->affaireService = $affaireService;
        $this->logger = $affaireLogger;

    }
    
    #[Route('/add-to-affaire', name: '_add_to_affaire')]
    public function addProduitToAffaire(
        Request $request
    ) {
        $idAffaire = $request->get('idAffaire');

        $affaire = $this->affaireService->find($idAffaire);

      
        $idProduit = $request->get('idProduit');

        $produitCategorie = $this->produitCategorieService->getCategorieById($idProduit);

        $qtt = $this->helpers->tofloat($request->get('qtt'));
        $typeVente = $request->get('typeVente');

        if ($typeVente == null || $typeVente == "") {
            $typeVente = "gros";
        }
        $data = [];
        $data['qtt'] = $qtt;
        $data['typeVente'] = $typeVente;
        
        //$prixHt = (null != $request->get('prixHt'))?$request->get('prixHt'): null;

        //$prixTTC = (null != $request->get('prixTTC'))?$request->get('prixTTC'): null;
        $coef = 1;
        if ($typeVente == "detail" && $produitCategorie->getVolumeGros() > 0) {
            $coef = $produitCategorie->getVolumeGros();
        }

        if($qtt > ($produitCategorie->getStockRestant() * $coef) ) {
            return new JsonResponse(['status' => 'error', 'message' => 'Le stock restant de ce produit est inférieur à la quantité que vous avez saisissez, veuillez changer la quantité à inférieur ou égale au stock restant'], Response::HTTP_OK);
        }
        
        $product = $this->productService->add($produitCategorie, $affaire, $data);

        //Mise à jour montant CA affaire

        //$montantCA = $produitService->updateCA($affaire, $produitRepository);

        /*$montantRemise = $affaire->getRemise();
        if (null == $montantRemise) {
            $montantRemise = 0;
        }*/

        //Cléf historique formationlycee rabelais
       
        $produits = $this->productService->findProduitAffaire($affaire);

        $facturesValide = [];
        if ($affaire->getPaiement() != null && count($affaire->getFactures()) > 0) {
            $factures = $affaire->getFactures();
            $facturesValide = $factures->filter(function ($item) use ($affaire) {
                return ($item->isValid() && 'regle' === $item->getStatut());
                
            });
        }

        // Obtenir l'utilisateur connecté
        $user = $this->getUser();

        // Créer le message de log en fonction de l'action
        $logMessage = ($affaire->getStatut() == 'devis') ? 'Produit ajouté au devis' : 'Produit ajouté à la commande';

        // Créer le log
        $this->logger->info($logMessage, [
          'Commande' => $affaire->getNom(),
          'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
          'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
          'ID Application' => $this->application->getId()
      ]);
       
        $template = "reloadFinanciereProduct.html.twig";
       
        return $this->render("admin/affaires/".$template, [
            'applicationId' => $this->application->getId(),
            'affaire' => $affaire,
            'produits' => $produits,
            'statuts' => $affaire::STATUT,
            'factureFile' => (count($facturesValide) > 0 ? $facturesValide[count($facturesValide) - 1]->getFile(): null)
            /*'projets' => $affaire::PROJET,
            'montantCA' => $montantCA,
            'affaireRemise' => $montantRemise,
            'tvaVentes' => $comptables,
            'cleCFA' => $cleCFA,
            'tabCleProduit' => $tabCleProduit,
            'application' => $this->application,
            /*'fraisTransport' => 0,
            'infosSuppls' => $affaireInfosSupplRepository->findOneBy(['affaire' => $affaire]),
            'baseTva' => $produitService->baseTva($produits, 'affaire')*/
        ]);
        

        //return new Response($produit->getId());
    }
    
    #[Route('/financiere/edit_produit', name: '_financiere_edit_tab_financiere')]
    public function editTabFinanciere(
        Request               $request,
        AffaireRepository     $affaireRepository
    )
    {

        $idProduit = $request->get('idProduit');

        $idAffaire = $request->get('idAffaire');

        $affaire = $this->affaireService->find($idAffaire);

        $product = $this->productService->getProductById($idProduit);
        
        $template = "tr_edit_financiere.html.twig";
        
        return $this->render('admin/affaires/' . $template, [
            'affaire' => $affaire,
            'product' => $product,
            'qttRestant' => ($product->getProduitCategorie()->getStockRestant() != null ? $product->getProduitCategorie()->getStockRestant() : 0),
            
        ]);
    }

    #[Route('/financiere/save/edit_produit', name: '_financiere_save_edit_product')]
    public function editProductAffaire(
        Request               $request
    )
    {

        $idProduit = $request->get('idProduit');

        $idAffaire = $request->get('idAffaire');

        $qtt = $request->get('qtt');

        $affaire = $this->affaireService->find($idAffaire);

        $product = $this->productService->getProductById($idProduit);
        
        if ($product) {
            $this->productService->setQttReserver($product,$qtt);
            $this->productService->persist($product);
            $this->productService->persist($product);
            $this->productService->update();
        }

        $facturesValide = [];
        if ($affaire->getPaiement() != null && count($affaire->getFactures()) > 0) {
            $factures = $affaire->getFactures();
            $facturesValide = $factures->filter(function ($item) use ($affaire) {
                return ($item->isValid() && 'regle' === $item->getStatut());
                
            });
        }

        $template = "reloadFinanciereProduct.html.twig";

        $produits = $this->productService->findProduitAffaire($affaire);
       
        return $this->render("admin/affaires/".$template, [
            'applicationId' => $this->application->getId(),
            'affaire' => $affaire,
            'produits' => $produits,
            'statuts' => $affaire::STATUT,
            'factureFile' => (count($facturesValide) > 0 ? $facturesValide[count($facturesValide) - 1]->getFile(): null)
            /*'projets' => $affaire::PROJET,
            'montantCA' => $montantCA,
            'affaireRemise' => $montantRemise,
            'tvaVentes' => $comptables,
            'cleCFA' => $cleCFA,
            'tabCleProduit' => $tabCleProduit,
            'application' => $this->application,
            /*'fraisTransport' => 0,
            'infosSuppls' => $affaireInfosSupplRepository->findOneBy(['affaire' => $affaire]),
            'baseTva' => $produitService->baseTva($produits, 'affaire')*/
        ]);
    }

    #[Route('/financiere/delete-produit', name: '_financiere_delete_product')]
    public function deleteProductAffaire(
        Request               $request
    )
    {

        $idProduit = $request->get('idProduit');

        $idAffaire = $request->get('idAffaire');

        $affaire = $this->affaireService->find($idAffaire);

        $product = $this->productService->getProductById($idProduit);
        if ($product) {
            $qtt = $product->getQtt();
            $this->productService->remove($product, $affaire);
            $this->productService->setQttReserver($product,$qtt, true);
            $this->productService->update();
        }

        $facturesValide = [];
        if ($affaire->getPaiement() != null && count($affaire->getFactures()) > 0) {
            $factures = $affaire->getFactures();
            $facturesValide = $factures->filter(function ($item) use ($affaire) {
                return ($item->isValid() && 'regle' === $item->getStatut());
                
            });
        }

        $template = "reloadFinanciereProduct.html.twig";

        $produits = $this->productService->findProduitAffaire($affaire);
       
        return $this->render("admin/affaires/".$template, [
            'applicationId' => $this->application->getId(),
            'affaire' => $affaire,
            'produits' => $produits,
            'statuts' => $affaire::STATUT,
            'factureFile' => (count($facturesValide) > 0 ? $facturesValide[count($facturesValide) - 1]->getFile(): null)
            /*'projets' => $affaire::PROJET,
            'montantCA' => $montantCA,
            'affaireRemise' => $montantRemise,
            'tvaVentes' => $comptables,
            'cleCFA' => $cleCFA,
            'tabCleProduit' => $tabCleProduit,
            'application' => $this->application,
            /*'fraisTransport' => 0,
            'infosSuppls' => $affaireInfosSupplRepository->findOneBy(['affaire' => $affaire]),
            'baseTva' => $produitService->baseTva($produits, 'affaire')*/
        ]);
    }
    
}
