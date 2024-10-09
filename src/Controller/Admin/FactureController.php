<?php

namespace App\Controller\Admin;

use App\Entity\Affaire;
use App\Entity\Facture;
use Psr\Log\LoggerInterface;
use App\Service\AccesService;
use App\Service\AffaireService;
use App\Service\FactureService;
use App\Service\ProductService;
use App\Service\ApplicationManager;
use App\Repository\FactureRepository;
use App\Exception\PropertyVideException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Repository\FactureEcheanceRepository;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Exception\UnsufficientPrivilegeException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/facture', name: 'factures')]
class FactureController extends AbstractController
{
    private $affaireService;
    private $accesService;
    private $application;
    private $productService;
    private $factureService;
    private $logger;
    private $factureRepo;
    private $factureEcheanceRepo;



    public function __construct(
        AffaireService $affaireService, 
        ApplicationManager $applicationManager, 
        AccesService $accesService, 
        ProductService $productService, 
        FactureService $factureService,
        LoggerInterface $affaireLogger, 
        FactureRepository $factureRepo,
        FactureEcheanceRepository $factureEcheanceRepo
        
        )
    {
        $this->affaireService = $affaireService;
        $this->accesService = $accesService;
        $this->productService = $productService;
        $this->application = $applicationManager->getApplicationActive();
        $this->factureService = $factureService;
        $this->logger = $affaireLogger;
        $this->factureRepo = $factureRepo;
        $this->factureEcheanceRepo = $factureEcheanceRepo;


    }

    #[Route('/', name: '_liste')]
    public function index(FactureService $factureService): Response
    {

        $data = [];
        try {
            $factures = $factureService->getAllFactures();
           
            $factureEcheanceData = [];  // Tableau pour stocker les échéances par facture
        
            $factureId = [];
            if ($factures != false) {
                foreach($factures as $facture) {
                    $factureId[] = $facture['id'];
                }

                $facts = $this->factureRepo->findBy(['id' => $factureId]);
                foreach($facts as $fact) {
                    $factureEcheances = $fact->getFactureEcheances(); 
                    
                    $countFactureEcheances = count($factureEcheances);
            
                    if ($countFactureEcheances > 0) {
                        $factureEcheanceId = $factureEcheances[$countFactureEcheances - 1]; 
                        $factureEcheance = $this->factureEcheanceRepo->findOneBy(['id' => $factureEcheanceId]);
            
                        // Stocke l'échéance pour cette facture
                        $factureEcheanceData[$fact->getId()] = $factureEcheance;
                    } else {
                        // Si pas d'échéance, stocke null
                        $factureEcheanceData[$fact->getId()] = null;
                    }
                }
            
                if ($factures == false) {
                    $factures = [];
                }
            } else {
                $factures = [];
            }
            
        
            
            
        
            $data["html"] = $this->renderView('admin/facture/index.html.twig', [
                'listes' => $factures,
                'factureEcheanceData' => $factureEcheanceData,
                'application' => $this->application
            ]);
        
            return new JsonResponse($data);
        } 
        catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }
    
    #[Route('/search', name: '_search')]
    public function search(Request $request, FactureService $factureService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        $genre = $request->request->get('genre');
        $nomCompte = $request->request->get('nomCompte');
        try {
            $factures = $factureService->getAllFactures();

            if ($factures == false) {
                $factures = [];
            }
            $data["html"] = $this->renderView('admin/facture/index_ajax.html.twig', [
                'listes' => $factures,
                'genre' => $genre
            ]);
            
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/search-facture-datatable/from-ajax', name: '_search_facture_datatable')]
    public function searchAjax(Request $request, SessionInterface $session, FactureService $factureService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        $genre = $request->request->get('genre');
        $genre = 1;
        $nomCompte = $request->get('nomCompte');
        $statutPaiement = $request->get('statutPaiement');
        $datePaieDu = $request->get('datePaieDu');
        $datePaieAu = $request->get('datePaieAu');
        
        $dateDu = $request->get('dateDu');
        $dateAu = $request->get('dateAu');
        $start = $request->get('start');
        $draw = $request->get('draw');
        $search = $request->get('search');
        $order = $request->get('order');
        $length = $request->get('length');
        try {
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
            }
            
            if ($start == 0) {
               
                $nbFacture = $factureService->searchFactureRawSql($genre, $nomCompte, $dateDu, $dateAu, null, $start, $length, null, true, $search, $statutPaiement, $datePaieDu, $datePaieAu);
              
                $session->set('nbFacture_'.$genre, $nbFacture);
            } else {
                $nbFacture = $session->get('nbFacture_'.$genre);
            }
         
           $facturesAssoc = $factureService->searchFactureRawSql($genre, $nomCompte, $dateDu, $dateAu, null, $start, $length, null, false, $search, $statutPaiement, $datePaieDu, $datePaieAu);
           //dd($facturesAssoc);
           $data = [];

            if ($facturesAssoc) {
                $k = 0;
                foreach ($facturesAssoc as $compteArray) {
                    
                    $data[$k][] = "<input type=\"checkbox\" class=\"custom-checkbox\" name=\"\" id=\"\">";
                    $isValid = "<a style=\"cursor: pointer;\"><i class=\"bi-check text-primary fs-4\"></i></a>";
                    if ($compteArray['isValid'] == 0) {
                        $isValid = "<a style=\"cursor: pointer;\"><i class=\"bi bi-x text-danger fs-4\"></i></a>";
                    }
                    
                    $data[$k][] = $isValid;
                   // 
                    $textAction = "<i class=\"bi bi-arrow-clockwise\"></i>
                                <div class=\"dropdown ms-2\">
                                    <a class=\"btn btn-outline-primary dropdown-toggle text-black\" href=\"#\" role=\"button\" id=\"dropdownMenuLink\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                                        <i class=\"bi bi-list\"></i>
                                    </a>
                                    <ul class=\"dropdown-menu\" aria-labelledby=\"dropdownMenuLink\">
                                        <li><a onclick=\"return openModalUpdatecompte(" . $compteArray['compteId'] . ");\" style=\"cursor: pointer;\" class=\"dropdown-item\">Modifier</a></li>
                                        <li><a onclick=\"return deleteCompte(" . $compteArray['compteId'] . ");\" style=\"cursor: pointer;\" class=\"dropdown-item\">Supprimer</a></li>
                                    </ul>
                                </div>";


                $data[$k][] = $textAction;
                $baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
                $fileFacture = $compteArray['fichier'];
                $file = ($fileFacture != "" && $fileFacture != null ? "$baseUrl/uploads/APP_'.$this->application->getId().'/factures/valide/$fileFacture": '#');
                if ($file != "#") {
                    $data[$k][] = "<a href=\"$file\" target=\"_blank\"><i class=\"bi bi-file-pdf-fill text-danger fs-4\"></i></a>";
                } else {
                    $data[$k][] = "Pas de facture générer";
                }
                
                $data[$k][] = $compteArray['dateCreation'];
                $data[$k][] = $compteArray['type'];
                $data[$k][] = $compteArray['numero'];

                $idCompte = $compteArray['compteId'];
                $data[$k][] = "<a href=\"" . $this->generateUrl('affaires_liste_affaire_from_facture', ['compte' => $compteArray['compteId']]) . "\">" . $compteArray['compte'] . "</a>";
                //$data[$k][] = "<a href=\"#\" onclick=\"return listAffaireByCompte($idCompte, 1, 'facture')\">" . $compteArray['compte'] . "</a>";
                $data[$k][] = "<a href=\"" . $this->generateUrl('affaires_financier_from_other_page', ['affaire' => $compteArray['affaireId']]) . "\">" . $compteArray['nomAffaire'] . "</a>";
               // $data[$k][] = $compteArray['prixTtc'];
                $data[$k][] = $compteArray['prixHt'];
                $data[$k][] = $compteArray['reglement'];
                $data[$k][] = Facture::STATUT[$compteArray['statut']];
                $data[$k][] = $compteArray['solde'];      
                $data[$k][] = Facture::ETAT[$compteArray['etat']];      
                $data[$k][] = $compteArray['remise'];      
                $k++;
                }
            }
           
            return new JsonResponse([
                'draw' => $draw,
                "recordsTotal" => $nbFacture,
                "recordsFiltered" => $nbFacture,
                "data" => $data
            ]);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }
    
    #[Route('/tout-exporter', name: '_tout_exporter')]
    public function exporterFacture(
        Request             $request, FactureService $factureService
    ) 
    {
        
        $nomCompte = $request->get('nom_compte');
        $genre = 1;
        $statutPaiement = $request->get('filter_status');
        $datePaieDu = $request->get('date_paiement_debut');
        $datePaieAu = $request->get('date_paiement_end');
     
        $tabStatut = ['Non payé'=> 'non',
            'Réglée'=> 'regle',
            'Annulée'=> 'annule',
            'En cours'=> 'encours',
            'En écheance'=> 'enecheance',
        'A régler'=> 'a_regle'];
            $statutPaiement = $tabStatut[$statutPaiement];
         
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
        }
        
        $tabFactures = $factureService->searchFactureRawSql($genre, $nomCompte, $dateDu, $dateAu, null, null, null, null, false, null, $statutPaiement, $datePaieDu, $datePaieAu, $factureList);
       // dd($facturesAssoc);
        $typeFacture = $request->get('type');
        $typeFacture = "Facture";
        //$tabFactures = [];
        //dd($_POST, $tabFactures);
        $tabChampPlus = [];

        
        $spreadsheet = new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Type');
        $sheet->setCellValue('C1', 'N°');
        $sheet->setCellValue('D1', 'Client');
        $sheet->setCellValue('E1', 'Commande');
        $sheet->setCellValue('F1', 'Reglement');
        $sheet->setCellValue('G1', 'Statut');


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

        $spreadsheet->getActiveSheet()->getStyle('A1:G1')->applyFromArray($styleArray);
        $spreadsheet->getActiveSheet()->getStyle("A1:G1")->getFont()->setBold(true);

        $sheet->setTitle("Export du " . date("Y-m-d"));

        

        if ($tabFactures) {
            $k = 2;

            foreach ($tabFactures as $facture) {
                $dateFacture = (null != $facture['dateCreation']) ? $facture['dateCreation'] : "";

                $type = $typeFacture;

                $numero = (null != $facture['numero']) ? $facture['numero'] : "";;
                
                $nomCompte = (null != $facture['compte']) ? $facture['compte'] : "";

                $nomAffaire = (null != $facture['nomAffaire']) ? $facture['nomAffaire'] : "";

                /*$soldeRestant = $facture->getPrixTtc() - $facture->getReglement();

                $prixTTC = number_format($facture->getPrixTtc(), 2, ".", "");

                $prixHt = number_format($facture->getPrixHt(), 2, ".", "");

                $tva = number_format($facture->getPrixTtc() - $facture->getPrixHt(), 2, ".", "");

              

                $tags = $facture->getTags();
                $textTag = "";
                foreach ($tags as $tag) {
                    $textTag .= $tag->getTag() . ", ";
                }*/


                /*if ($typeFacture == "FactureFournisseur") {
                    $sheet->setCellValue('A' . $k, $dateFacture);
                    $sheet->setCellValue('B' . $k, $numero);
                    $sheet->setCellValue('C' . $k, $nomCompte);
                    $sheet->setCellValue('D' . $k, $nomAffaire);
                    $sheet->setCellValue('E' . $k, $prixHt);
                    $sheet->setCellValue('F' . $k, $tva);
                    $sheet->setCellValue('G' . $k, $prixTTC);
                } else {*/
                    $sheet->setCellValue('A' . $k, $dateFacture);
                    $sheet->setCellValue('B' . $k, $type);
                    $sheet->setCellValue('C' . $k, $numero);
                    $sheet->setCellValue('D' . $k, $nomCompte);
                    $sheet->setCellValue('E' . $k, $nomAffaire);
                    $sheet->setCellValue('F' . $k, $facture['reglement']);
                    $sheet->setCellValue('G' . $k, $facture['statut']);
                //}


                $k++;
            }
            $sheet->getStyle('A2:G' . $k)->applyFromArray($styleAlignArray);
            $sheet->getStyle('A1:G1')->applyFromArray($styleArray);
            $sheet->getStyle("A1:G1")->getFont()->setBold(true);
            $sheet->getStyle('A1:G' . $k)->getAlignment()->setWrapText(true);
            foreach (range('A1:G' . $k, $sheet->getHighestDataColumn()) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }


        $fileName = 'Factures_' . $this->application . '_' . date('Y-m-d h:i:s') . '.xlsx';
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system

        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/new/{affaire}', name: '_create_facture')]
    public function create(
        Affaire $affaire, 
        Request $request, 
        SessionInterface $session,
        ProductService $productService)
    {
        $data = [];
        try {
            $session->set('idAffaire', $affaire->getId());
            $produits = $productService->findProduitAffaire($affaire);
            if ($produits == false) {
                $produits = [];
            }
            
            $data["html"] = $this->renderView('admin/facture/new.html.twig', [
                'produits' => $produits,
                'affaire' => $affaire
            ]);
           
            return new JsonResponse($data);
        } catch (PropertyVideException $PropertyVideException) {
            $data['exception'] = $PropertyVideException->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        } 
        return new JsonResponse($data);
    }

    #[Route('/delete/{facture}', name: '_delete')]
    public function delete(Request $request, Facture $facture)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                if($facture->getStatut() == 'regle' && $facture->getAffaire()->getPaiement() == "paye") {
                    return new JsonResponse(['status' => 'error', 'message' => 'Vous ne pouvez pas supprimer une facture qui est déjà payé'], Response::HTTP_OK);
                } else {
                    $this->factureService->delete($facture);
                    return new JsonResponse(['status' => 'success', 'message' => 'Facture supprimée avec succès'], Response::HTTP_OK);
                }
                
            }
                
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
        } catch (ServerException $ServerException) {
            $this->createNotFoundException('Exception' . $ServerException->getMessage());
        } catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
            $this->createNotFoundException('Exception' . $NotNullConstraintViolationException->getMessage());
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
        }
    }

   
}
