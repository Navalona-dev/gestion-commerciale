<?php

namespace App\Controller\Admin;

use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\ProduitType;
use App\Service\AccesService;
use App\Service\ExcelImporter;
use App\Entity\ProduitCategorie;
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

                $batchSize = 20; // Ajustez la taille du batch selon vos besoins
                $i = 0;

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

                    $categories = $this->categorieRepository->findNameCategoriesByApplication($this->application);

                    $existingCategorie = $this->categorieRepository->findOneBy(['nom' => $dataCategorie, 'application' => $this->application]);
                    $categorieName = $existingCategorie ? $existingCategorie->getNom() : null;

                    if ($dataCategorie !== null) {
                        $categorie = null;
                    //dd($dataCategorie, $categorieName,in_array($categorieName, array_column($categories, 'nom')), array_column($categories, 'nom'), $categories);
                        // Vérifie si $categorieName est dans $categories
                        if ($categorieName !== null && in_array($categorieName, array_column($categories, 'nom'))) {
                            $categorie = $existingCategorie;   
                            //dd('hello');           
                        } else {
                            //dd('cc');
                            // Si la catégorie n'existe pas dans la base de données
                            $categorie = new Categorie();
                            $categorie->setNom($dataCategorie);
                            $categorie->setDateCreation($date);
                            $categorie->setApplication($this->application);
                            $this->em->persist($categorie);
                        }
                        
                    }
                    //dd('ici');
                    //$this->em->flush();
                        //dd($dataCategorie, $categorieName, $categories);


                    //traiter le type
                    $dataType = isset($dataProduct[2]) ? trim($dataProduct[2]) : null;
                    $types = $this->typeRepository->findNameTypeByApplication($this->application);

                    $existingType = $this->typeRepository->findOneBy(['nom' => $dataType, 'application' => $this->application]);
                    $typeName = $existingType ? $existingType->getNom() : null;

                    if($dataType !== null) {

                        $type = null;
    
                        //si le type existe déjà dans la base de données
                        if($typeName !== null && in_array($typeName, array_column($types, 'nom'))) {
                            $type = $existingType;
                        } else {
                            $type = new ProduitType();
                            $type->setNom($dataType);
                            $type->setDateCreation($date);
                            $type->setApplication($this->application);
                            $this->em->persist($type);
                        }
                    }
                    

                    //traiter le compte
                    $dataCompte = isset($dataProduct[1]) ? trim($dataProduct[1]) : null;
                    $comptes = $this->compteRepository->findNameCompteByApplication($this->application);
                    $existingCompte = $this->compteRepository->findOneBy(['nom' => $dataCompte, 'application' => $this->application, 'genre' => 2]);
                    $compteName = $existingCompte ? $existingCompte->getNom() : null;

                    if ($dataCompte !== null) {
                        $compte = null;
                        if ($compteName !== null && in_array($compteName, array_column($comptes, 'nom'))) {
                            $compte = $existingCompte;
                        } else {
                            $compte = new Compte();
                            $compte->setNom($dataCompte);
                            $compte->setApplication($this->application);
                            $compte->setDateCreation($date);
                            $compte->setGenre(2);
                            $this->em->persist($compte);
                        }
                    }

                    //traiter le produit
                    $dataReference = isset($dataProduct[4]) ? trim($dataProduct[4]) : null;
                    $produits = $this->produitCategorieRepo->findReferenceProduitByApplication($this->application);
                    $existingProduitCategorie = $this->produitCategorieRepo->findOneBy(['reference' => $dataReference, 'application' => $this->application]);
                    $produitReference = $existingProduitCategorie ? $existingProduitCategorie->getReference() : null;
          

                    if($dataReference !== null) {
                        $produitCategorie = null;
                        if($produitReference !== null && in_array($produitReference, array_column($produits, 'reference'))) {
                            $produitCategorie = $existingProduitCategorie;
                        } else {
                            $produitCategorie = new ProduitCategorie();
    
                            $produitCategorie->setCategorie($categorie ?? null);
                            $produitCategorie->addCompte($compte ?? null);
                            $produitCategorie->setType($type ?? null);
                            $produitCategorie->setNom($dataProduct[3]);
                            $produitCategorie->setReference($dataProduct[4] ?? null);
                            $produitCategorie->setPresentationGros($dataProduct[5] ?? null);
                            $produitCategorie->setPrixAchat(floatval($dataProduct[6] ?? 0.0));
                            $produitCategorie->setPrixVenteGros(floatval($dataProduct[7] ?? 0.0));
                            $produitCategorie->setPrixVenteDetail(floatval($dataProduct[8] ?? 0.0));
                            $produitCategorie->setPresentationDetail($dataProduct[9]);
                            $produitCategorie->setApplication($this->application);
            
                            $produitCategorie->setDateCreation($date);
            
                            $this->em->persist($produitCategorie);
                        }
                    }
                    $this->em->flush();
                    dd('ici');
                    // Batch processing
                    if (($i % $batchSize) === 0) {
                        $this->em->flush(); // Flush the batch to the database
                        $this->em->clear(); // Detach all objects from Doctrine to free up memory
                    }
                    $i++;
                }
    
                $this->em->flush();
    
                $this->addFlash('success', 'Importation produit avec succès.');
                return $this->redirectToRoute('imports_liste');
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
