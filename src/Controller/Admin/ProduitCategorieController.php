<?php

namespace App\Controller\Admin;

use Exception;
use App\Entity\Stock;
use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\ProduitType;
use App\Form\TransfertType;
use App\Service\LogService;
use Psr\Log\LoggerInterface;
use App\Service\AccesService;
use App\Entity\ProduitCategorie;
use App\Form\ProduitCategorieType;
use App\Service\ApplicationManager;
use App\Form\UpdatePriceProductType;
use App\Repository\ProductRepository;
use App\Repository\CategorieRepository;
use App\Exception\PropertyVideException;
use App\Form\UpdateProduitCategorieType;
use App\Service\ProduitCategorieService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
use App\Repository\ProduitTypeRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProduitCategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/produit/categorie', name: 'produit_categories')]
class ProduitCategorieController extends AbstractController
{
    private $accesService;
    private $produitCategorieService;
    private $application;
    private $logger;
    private $logService;
    private $categorieRepository;
    private $entityManager;
    private $typeRepository;

    public function __construct(
        AccesService $AccesService, 
        ApplicationManager $applicationManager, 
        ProduitCategorieService $produitCategorieService,
        LoggerInterface $productLogger,
        LogService $logService,
        CategorieRepository $categorieRepository,
        EntityManagerInterface $entityManager,
        ProduitTypeRepository $typeRepository
        )
    {
        $this->accesService = $AccesService;
        $this->produitCategorieService = $produitCategorieService;
        $this->application = $applicationManager->getApplicationActive();
        $this->logger = $productLogger;
        $this->logService = $logService;
        $this->categorieRepository = $categorieRepository;
        $this->entityManager = $entityManager;
        $this->typeRepository = $typeRepository;

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
            
            $form = $this->createForm(ProduitCategorieType::class, $produitCategorie, []);
            $fournisseurs = $this->produitCategorieService->getAllFournisseur();
            
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {

                    $formData = $form->getData();
                    
                    $idFournisseur = $request->get("produit_categorie_compte");

                    list($produitCategorie, $error) = $this->produitCategorieService->add($produitCategorie, $this->application, $idFournisseur, $formData);

                    if($error == true) {
                        return new JsonResponse(['status' => 'error'], Response::HTTP_OK);
                    } else {
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
                        $data["qtt"] = $produitCategorie->getStockRestant();
                        $data["stockRestant"] = $produitCategorie->getStockRestant();
                        $data["fournisseur"] = ($produitCategorie->getReference() != false && $produitCategorie->getReference() != null ? $produitCategorie->getReference() : null);
                        $data["typeSource"] = "Point de vente";
                        $data["typeDestination"] = "Point de vente";;
                        $data["commande"] = null;
                        $data["commandeId"] = null;
                        $data["sourceId"] =  $this->application->getId();
                        $data["destinationId"] = $this->application->getId();
                        $this->logService->addLog($request, "reception", $this->application->getId(), $produitCategorie->getReference(), $data);
                        
                        
                        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                        $this->addFlash('success', 'Création produit categorie "' . $produitCategorie->getNom() . '" avec succès.');
                        return $this->redirectToRoute('produit_categories_liste');
                    }

                }
        
                
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

    #[Route('/add/categorie', name: '_add_categorie')]
    public function addCategorie(Request $request)
    {
        $data = []; 
    
        try {
            // Récupérez le nom envoyé via la requête AJAX
            $nom = $request->request->get('nom'); 
    
            // Créez une nouvelle instance de categorie
            $categorie = new Categorie();
            
            // Si vous avez un setter pour le nom dans categorie$categorie, utilisez-le
            $categorie->setNom($nom); 
            $categorie->setApplication($this->application);
            $categorie->setDateCreation(new \DateTime());
    
            $this->entityManager->persist($categorie);
            $this->entityManager->flush();

            $data['id'] = $categorie->getId();
    
            return new JsonResponse($data);
        } catch (PropertyVideException $PropertyVideException) {
            $data['exception'] = $PropertyVideException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        } catch (UniqueConstraintViolationException $UniqueConstraintViolationException) {
            return new JsonResponse(['error' => 'Ce nom de catégorie existe déjà.'], 400);
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            return new JsonResponse($data);
        }
        
        return new JsonResponse($data);
    }

    #[Route('/add/type', name: '_add_type')]
    public function addType(Request $request)
    {
        $data = []; 
    
        try {
            // Récupérez le nom envoyé via la requête AJAX
            $nom = $request->request->get('nom'); 
    
            // Créez une nouvelle instance de type
            $type = new ProduitType();
            
            // Si vous avez un setter pour le nom dans type, utilisez-le
            $type->setNom($nom); 
            $type->setApplication($this->application);
            $type->setDateCreation(new \DateTime());
    
            $this->entityManager->persist($type);
            $this->entityManager->flush();

            $data['id'] = $type->getId();
    
            return new JsonResponse($data);
        } catch (PropertyVideException $PropertyVideException) {
            $data['exception'] = $PropertyVideException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        } catch (UniqueConstraintViolationException $UniqueConstraintViolationException) {
            return new JsonResponse(['error' => 'Ce nom de catégorie existe déjà.'], 400);
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            return new JsonResponse($data);
        }
        
        return new JsonResponse($data);
    }

    #[Route('/add/compte', name: '_add_compte')]
    public function addCompte(Request $request)
    {
        $data = []; 
    
        try {
            // Récupérez le nom envoyé via la requête AJAX
            $nom = $request->request->get('nom'); 
    
            // Créez une nouvelle instance de type
            $compte = new Compte();
            
            // Si vous avez un setter pour le nom dans compte, utilisez-le
            $compte->setNom($nom); 
            $compte->setApplication($this->application);
            $compte->setGenre(2);
            $compte->setDateCreation(new \DateTime());
    
            $this->entityManager->persist($compte);
            $this->entityManager->flush();

            $data['id'] = $compte->getId();
    
            return new JsonResponse($data);
        } catch (PropertyVideException $PropertyVideException) {
            $data['exception'] = $PropertyVideException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        } catch (UniqueConstraintViolationException $UniqueConstraintViolationException) {
            return new JsonResponse(['error' => 'Ce nom de catégorie existe déjà.'], 400);
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            return new JsonResponse($data);
        }
        
        return new JsonResponse($data);
    }

    #[Route('/delete-selection', name: '_delete_selection')]
    public function deleteSelection(Request $request)
    {
        try {
            if ($request->isXmlHttpRequest()) {
                // Récupérer les IDs des produits depuis la requête AJAX
                $productIds = $request->request->get('productIds');
                if (is_string($productIds)) {
                    $productIds = explode(',', $productIds); // Convertir en tableau à partir d'une chaîne
                }

                if (!empty($productIds)) {
                    // Récupérer les objets ProduitCategorie en fonction des IDs
                    $produitCategories = $this->entityManager->getRepository(ProduitCategorie::class)->findBy(['id' => $productIds]);

                    if (count($produitCategories) > 0) {
                        // Appel du service pour supprimer les produits
                        $this->produitCategorieService->removeMultiple($produitCategories);

                        return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                    } else {
                        return new JsonResponse(['status' => 'error', 'message' => 'Aucun produit trouvé.'], Response::HTTP_OK);
                    }
                } else {
                    return new JsonResponse(['status' => 'error', 'message' => 'Aucun produit sélectionné.'], Response::HTTP_BAD_REQUEST);
                }
            }
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    #[Route('/inventaire/{produitCategorie}', name: '_inventaire')]
    public function inventaire(Request $request, ProduitCategorie $produitCategorie)
    {
        $request->getSession()->set('produitCategorieId', $produitCategorie->getId());
        $data = [];
        try {
            $logFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/APP_'.$this->application->getId().'/historique/';
            $prefix = 'log_'.$this->application->getId().'_'.$produitCategorie->getReference();
           
            $parsedLines = $this->logService->getContentLog($logFilePath, $prefix);
              //dd($parsedLines, $prefix, $produitCategorie);
            $htmlContent = $this->renderView('admin/produit_categorie/inventaire.html.twig', [
                'logLines' => $parsedLines,
                'logEmpty' => false,
                'produitCategorie' => $produitCategorie
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

    #[Route('/inventaire/tout-exporter/{produitCategorie}', name: '_inventaire_tout_exporter')]
    public function exporterInventaire(
        Request             $request,
        ProduitCategorie $produitCategorie
    ) {
        
        /*$nomCompte = $request->get('nom_compte');
        $genre = 1;
        $statutPaiement = $request->get('filter_satatus');
        $datePaieDu = $request->get('date_paiement_debut');
        $datePaieAu = $request->get('date_paiement_end');
        
        $dateDu = $request->get('date_facture_debut');
        $dateAu = $request->get('date_facture_end');

        $factureList = $request->get('factureList');

        if (null != $datePaieDu && "" != $datePaieDu) {
            $datePaieDuExplode = explode("/", $datePaieDu);
            $datePaieDu = new \DateTime($datePaieDuExplode[2] . "-" . $datePaieDuExplode[1] . "-" . $datePaieDuExplode[0]);
        }
       
        if (null != $datePaieAu && "" != $datePaieAu) {
            $datePaieAuExplode = explode("/", $datePaieAu);
            $datePaieAu = new \DateTime($datePaieAuExplode[2] . "-" . $datePaieAuExplode[1] . "-" . $datePaieAuExplode[0]);
        }

        if (null != $dateDu && "" != $dateDu) {
            $dateDuExplode = explode("/", $dateDu);
            $dateDu = new \DateTime($dateDuExplode[2] . "-" . $dateDuExplode[1] . "-" . $dateDuExplode[0]);
        }

        $dateAu = $request->get('dateAu');
       
        if (null != $dateAu && "" != $dateAu) {
            $dateAuExplode = explode("/", $dateAu);
            $dateAu = new \DateTime($dateAuExplode[2] . "-" . $dateAuExplode[1] . "-" . $dateAuExplode[0]);
        }*/
        //$tabFactures = $factureService->searchFactureRawSql($genre, $nomCompte, $dateDu, $dateAu, null, null, null, null, false, null, $statutPaiement, $datePaieDu, $datePaieAu);
       // dd($facturesAssoc);
       $logFilePath = $this->getParameter('kernel.project_dir') . '/public/uploads/APP_'.$this->application->getId().'/historique/';
            $prefix = 'log_'.$this->application->getId().'_'.$produitCategorie->getReference();
           
            $parsedLines = $this->logService->getContentLog($logFilePath, $prefix);
            
        $typeFacture = $request->get('type');
        $typeFacture = "Facture";
        //$tabFactures = [];

        $tabChampPlus = [];

        
        $spreadsheet = new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();

        
        $sheet->setCellValue('A1', 'Produit/Fournisseur');
        $sheet->setCellValue('B1', 'Action');
        $sheet->setCellValue('C1', 'Utilisateur');
        $sheet->setCellValue('D1', 'Source/Destination');
        $sheet->setCellValue('E1', 'Quantité/Stock Restant');
        $sheet->setCellValue('F1', 'Date reception');
        $sheet->setCellValue('G1', 'Date transfert');
        $sheet->setCellValue('H1', 'Date de sortie');


        $styleArray = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => '1ab394'),
                )
            )
        );
        $styleAlignArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => '000000'),
                ]
            ],
        ];

        $spreadsheet->getActiveSheet()->getStyle('A1:H1')->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle("A1:H1")->getFont()->setBold(true);

        $sheet->setTitle("Export du " . date("Y-m-d"));

        

        if ($parsedLines) {
            $k = 2;

            foreach ($parsedLines as $infoLine) {
                    $sheet->setCellValue('A' . $k, $infoLine['produit']."/".$infoLine['fournisseur']);
                    $sheet->setCellValue('B' . $k, $infoLine['action']);
                    $sheet->setCellValue('C' . $k, $infoLine['userDoAction']);
                    $sheet->setCellValue('D' . $k, $infoLine['source']."/".$infoLine['destination']);
                    $sheet->setCellValue('E' . $k, $infoLine['qtt']."/".$infoLine['stockRestant']);
                    $sheet->setCellValue('F' . $k, $infoLine['dateReception']);
                    $sheet->setCellValue('G' . $k, $infoLine['dateTransfert']);
                    $sheet->setCellValue('H' . $k, $infoLine['dateSortie']);
                //}


                $k++;
            }
            $sheet->getStyle('A2:H' . $k)->applyFromArray($styleAlignArray);
            $sheet->getStyle('A1:H1')->applyFromArray($styleArray);
            $sheet->getStyle("A1:H1")->getFont()->setBold(true);
            $sheet->getStyle('A1:H' . $k)->getAlignment()->setWrapText(true);
            foreach (range('A1:H' . $k, $sheet->getHighestDataColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }


        $fileName = 'Inventaires_produit' . $this->application . '_' . date('Y-m-d h:i:s') . '.xlsx';
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system

        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
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
                if (count($produitCategorie->getProduits()) == 0) {
                    $this->produitCategorieService->remove($produitCategorie);
                    $this->produitCategorieService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } else {
                    return new JsonResponse(['status' => 'error'], Response::HTTP_OK);
                }
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

                $produitCategories = $produitCategorieRepo->findBy(['application' => $newApplication]);

                // Condition pour vérifier si $produitReference existe dans $produitCategories
                $productReferenceExists = null;
                foreach ($produitCategories as $produit) {
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
                
                $produitCategorieService->updateStockRestant($oldProduitCategorie, $quantity, $this->application);

                if ($request->isXmlHttpRequest()) {

                    $produitCategorieService->addNewProductForNewApplication($productReferenceExists, $oldProduitCategorie, $quantity, $newApplication, $isChangePrice);

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
                'applicationName' => $oldApplicationName,
                'produitCategorie' => $oldProduitCategorie

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
