<?php

namespace App\Controller\Admin;

use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\ProduitType;
use App\Service\AccesService;
use App\Service\ExcelImporter;
use App\Entity\ProduitCategorie;
use App\Entity\Stock;
use App\Service\ApplicationManager;
use App\Repository\CompteRepository;
use App\Repository\CategorieRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitCategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/import/produit', name: 'imports')]
class ImportProduitController extends AbstractController
{
    private $accesService;
    private $application;
    private $excelImporter;
    private $categorieRepository;
    private $typeRepository;
    private $compteRepository;
    private $em;
    private $produitCategorieRepo;

    public function __construct(
        ApplicationManager $applicationManager, 
        AccesService $accesService,
        ExcelImporter $excelImporter,
        CategorieRepository $categorieRepository,
        ProduitTypeRepository $typeRepository,
        CompteRepository $compteRepository,
        EntityManagerInterface $em,
        ProduitCategorieRepository $produitCategorieRepo)
    {
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
        $this->excelImporter = $excelImporter;
        $this->categorieRepository = $categorieRepository;
        $this->typeRepository = $typeRepository;
        $this->compteRepository = $compteRepository;
        $this->em = $em;
        $this->produitCategorieRepo = $produitCategorieRepo;
    }

    #[Route('/', name: '_liste')]
    public function index(Request $request): Response
    {
        $data = [];
        ini_set('memory_limit', '512M'); // Augmenter la limite de mémoire
        try {
            
            if ($request->files->get('file')) {
                $file = $request->files->get('file');
                $spreadsheet = IOFactory::load($file);

                $sheet = $spreadsheet->getActiveSheet();
              
                foreach ($sheet->getRowIterator() as $index => $row) {
                    if ($index == 1) {
                        continue;
                    }
    
                    $dataProduct = [];
    
                    foreach ($row->getCellIterator() as $cell) {
                        $dataProduct[] = $cell->getValue();
                    }
                    $date = new \DateTime();

                    // Traiter la catégorie
                    $dataCategorie = isset($dataProduct[0]) ? trim($dataProduct[0]) : null;
                    $dataCategorie = trim($dataCategorie);
                    
                    $existingCategorie = $this->categorieRepository->findOneBy(['nom' => $dataCategorie, 'application' => $this->application]);
                 
                    if ($dataCategorie !== null) {
                       
                    //dd($dataCategorie, $categorieName,in_array($categorieName, array_column($categories, 'nom')), array_column($categories, 'nom'), $categories);
                        // Vérifie si $categorieName est dans $categories
                 
                        if ($existingCategorie == null) {
                            $categorie = null;
                            // Si la catégorie n'existe pas dans la base de données
                            $categorie = new Categorie();
                            $categorie->setNom($dataCategorie);
                            $categorie->setDateCreation($date);
                            $categorie->setApplication($this->application);
                            $this->em->persist($categorie);
                        } else {
                            $categorie = $existingCategorie;
                        }
                        
                    }
                    //$this->em->flush();
                    //dd($existingCategorie, $categorie);

                    //traiter le type
                    $dataType = isset($dataProduct[2]) ? trim($dataProduct[2]) : null;
                    
                    $existingType = $this->typeRepository->findOneBy(['nom' => $dataType, 'application' => $this->application]);
                    
                    if($dataType !== null) {

                        $type = null;
    
                        //si le type existe déjà dans la base de données
                        if ($existingType == null) {
                            $type = new ProduitType();
                            $type->setNom($dataType);
                            $type->setDateCreation($date);
                            $type->setApplication($this->application);
                            $type->setIsActive(true);
                            $this->em->persist($type);
                        } else {
                            $type = $existingType;
                        }
                    }
                   
                    //traiter le compte
                    $dataCompte = isset($dataProduct[1]) ? trim($dataProduct[1]) : null;
                    
                    $existingCompte = $this->compteRepository->findOneBy(['nom' => $dataCompte, 'application' => $this->application, 'genre' => 2]);
                    
                    if ($dataCompte !== null) {
                        $compte = null;
                        if ($existingCompte == null) {
                            $compte = new Compte();
                            $compte->setNom($dataCompte);
                            $compte->setApplication($this->application);
                            $compte->setDateCreation($date);
                            $compte->setGenre(2);
                            $this->em->persist($compte);
                        } else {
                            $compte = $existingCompte;
                        }
                    }
                    

                    //traiter le produit
                    $dataReference = isset($dataProduct[4]) ? trim($dataProduct[4]) : null;
                    
                    $existingProduitCategorie = $this->produitCategorieRepo->findOneBy(['reference' => $dataReference, 'application' => $this->application]);
                   
                    if($dataReference !== null) {
                        $produitCategorie = null;
                        if ($existingProduitCategorie == null) {
                            $produitCategorie = new ProduitCategorie();
    
                            $produitCategorie->setCategorie($categorie ?? null);
                            $produitCategorie->addCompte($compte ?? null);
                            $produitCategorie->setType($type ?? null);
                            $produitCategorie->setNom($dataProduct[3]);
                            $produitCategorie->setReference($dataProduct[4] ?? null);
                            $produitCategorie->setPresentationGros($dataProduct[5] ?? null);
                            $produitCategorie->setUniteVenteGros($dataProduct[6] ?? null);
                            $produitCategorie->setVolumeGros(floatval($dataProduct[7]) ?? 0.0);
                            $produitCategorie->setPrixAchat(floatval($dataProduct[8] ?? 0.0));
                            
                            $produitCategorie->setPrixVenteGros(floatval($dataProduct[9] ?? 0.0));
                            $produitCategorie->setPrixVenteDetail(floatval($dataProduct[10] ?? 0.0));
                            $produitCategorie->setPresentationDetail($dataProduct[11]);
                            $produitCategorie->setUniteVenteDetail($dataProduct[12]);
                            $produitCategorie->setVolumeDetail(floatval($dataProduct[13]));
                            $produitCategorie->setApplication($this->application);
            
                            $produitCategorie->setDateCreation($date);
                            $produitCategorie->setStockMin(10);
                            $produitCategorie->setStockMax(50);
                            
                            $stock = new Stock();
                            $stockRestant = isset($dataProduct[14]) ? trim($dataProduct[14]) : null;
                            $stock->setDateCreation($date);
                            if (null != $stockRestant) {
                                $stock->setQtt(floatval($stockRestant));
                            } else {
                                $stock->setQtt(0);
                            }
                            $produitCategorie->setStockRestant(floatval($stockRestant));
                            $produitCategorie->addStock($stock);
                            $this->em->persist($stock);
                            $this->em->persist($produitCategorie);
                            
                        } else {
                            $produitCategorie = $existingProduitCategorie;
                            //$produitCategorie->getStock()
                        }
                    }
                    $this->em->flush();
                }
                
                $this->addFlash('success', 'Importation produit avec succès.');
                return $this->redirect("/admin#tab-produit-categorie");
            }

           
            $data["html"] = $this->renderView('admin/import_produit/index.html.twig', [
                
            ]);
           
            return new JsonResponse($data);

        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }
}
