<?php

namespace App\Controller\Admin;

use App\Entity\Facture;
use App\Service\FactureService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/admin/facture', name: 'factures')]
class FactureController extends AbstractController
{
    #[Route('/', name: '_liste')]
    public function index(FactureService $factureService): Response
    {

        $data = [];
        try {
            
            $factures = $factureService->getAllFactures();
            if ($factures == false) {
                $factures = [];
            }
           
            $data["html"] = $this->renderView('admin/facture/index.html.twig', [
                'listes' => $factures,
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
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
                $file = ($fileFacture != "" && $fileFacture != null ? "$baseUrl/uploads/factures/valide/$fileFacture": '#');
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
    
}
