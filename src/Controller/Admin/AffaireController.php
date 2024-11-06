<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Compte;
use App\Form\UserType;
use App\Entity\Affaire;
use App\Entity\Facture;
use App\Entity\Product;
use App\Form\CompteType;
use App\Form\ProfilType;
use App\Form\AffaireType;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Form\FicheCompteType;

use App\Service\AccesService;
use App\Form\ProductDepotType;
use App\Service\CompteService;
use App\Service\DateFormatter;
use App\Form\AccesExtranetType;
use App\Service\AffaireService;
use App\Service\FactureService;
use App\Service\ProductService;
use App\Service\CategorieService;
use App\Service\ApplicationManager;
use App\Repository\CompteRepository;
use App\Repository\AffaireRepository;
use App\Repository\FactureRepository;
use App\Repository\ProductRepository;
use App\Exception\PropertyVideException;
use App\Service\ProduitCategorieService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ApplicationRepository;
use App\Repository\FactureEcheanceRepository;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\UnsufficientPrivilegeException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/affaires', name: 'affaires')]
class AffaireController extends AbstractController
{
    private $affaireService;
    private $accesService;
    private $application;
    private $productService;
    private $factureService;
    private $logger;
    private $factureRepo;
    private $factureEcheanceRepo;
    private $applicationRepo;
    private $affaireRepo;
    private $em;
    private $productRepo;
    private $dateFormatter;

    public function __construct(
        AffaireService $affaireService, 
        ApplicationManager $applicationManager, 
        AccesService $accesService, 
        ProductService $productService, 
        FactureService $factureService,
        LoggerInterface $affaireLogger, 
        FactureRepository $factureRepo,
        FactureEcheanceRepository $factureEcheanceRepo,
        ApplicationRepository $applicationRepo,
        AffaireRepository $affaireRepo,
        EntityManagerInterface $em,
        ProductRepository $productRepo,
        DateFormatter $dateFormatter
        
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
        $this->applicationRepo = $applicationRepo;
        $this->affaireRepo = $affaireRepo;
        $this->em = $em;
        $this->productRepo = $productRepo;
        $this->dateFormatter = $dateFormatter;

    }

    #[Route('/', name: '_liste_affaire')]
    public function liste(Request $request, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        try {

            $statut = $request->request->get('statut');

            $affaires = $this->affaireService->getAffaires($statut);

            if ($affaires == false) {
                $affaires = [];
            }

            if($statut == "devis") {
                $data["html"] = $this->renderView('admin/affaires/devis.html.twig', [
                    'listes' => $affaires,
                ]);
            } elseif($statut == "commande") {
                $data["html"] = $this->renderView('admin/affaires/commande.html.twig', [
                    'listes' => $affaires,
                ]);
            }
            
            

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/refresh', name: '_liste_refresh')]
    public function indexRefresh(
        CompteService $compteService, 
        Request $request, 
        SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $compteId = $session->get('compte');
       
        $compte = null;
        if (null != $compteId) {
            $compte = $compteService->find($compteId);
        }
        
        $data = [];
        try {
            if (null != $compte) {
                $affaires = $this->affaireService->getAllAffaire($compte);

                if ($affaires == false) {
                    $affaires = [];
                }

                $genre = $compte->getGenre();
                
                if($genre == 1) {
                    $data["html"] = $this->renderView('admin/affaires/index_client.html.twig', [
                        'listes' => $affaires,
                        'compte' => $compte,
                        'genre' => $genre,
                        'count' => count($affaires)
                       
                    ]);
                } elseif($genre == 2) {
                    $data["html"] = $this->renderView('admin/affaires/index_fournisseur.html.twig', [
                        'listes' => $affaires,
                        'compte' => $compte,
                        'genre' => $genre,
                        'count' => count($affaires)
                       
                    ]);
                }
                // Ajoute la clé 'count' au tableau de données
                $data['count'] = count($affaires);

                return new JsonResponse($data);

            } else  {
                throw new \Exception("Compte introuvable");
            }
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/depot/valid/multiple', name: '_depot_valid_multiple')]
    public function productDepotMultiple(Request $request)
    {
        $idAffaire = $request->getSession()->get('idAffaire');
        $affaire = $this->affaireRepo->findOneBy(['id' => $idAffaire]);
        $products = $this->productRepo->getAllProducts($affaire->getId());

        $data = [];
        try {

            if ($request->isXmlHttpRequest()) {

                $productIds = $request->request->get('productIds');
                if (is_string($productIds)) {
                    $productIds = explode(',', $productIds); // Convertir en tableau à partir d'une chaîne
                }

                $qttVendus = $request->request->get('qttVendus');
                if (is_string($qttVendus)) {
                    $qttVendus = explode(',', $qttVendus); // Convertir en tableau à partir d'une chaîne
                }

                if (!empty($productIds)) {
                    // Récupérer les objets ProduitCategorie en fonction des IDs
                    $productsSelectionner = $this->em->getRepository(Product::class)->findBy(['id' => $productIds]);
                
                    $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/valide/';

                    // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
                    if (!is_dir($documentFolder)) {
                        mkdir($documentFolder, 0777, true); // 0777 pour les permissions, et `true` pour créer récursivement les sous-dossiers
                    }
                
                    list($pdfContent, $facture) = $this->factureService->validDepotMultiple($affaire, $documentFolder, $request, $qttVendus, $productsSelectionner);
                    
                    $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";

                    $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/valide/' . $filename;
                    
                    // Sauvegarder le fichier PDF
                    file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                    
                    return new JsonResponse([
                        'status' => 'success',
                        'pdfUrl' => $pdfPath,
                    ]);
                }

            }


            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/affaires/modal_depot_multiple.html.twig', [
                'affaire' => $affaire,
                'products' => $products
            ]);
           
            return new JsonResponse($data);

        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        } 

        return new JsonResponse($data);
    }

    #[Route('/{compte}', name: '_liste')]
    public function index(Compte $compte, Request $request, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        try {

            $affaires = $this->affaireService->getAllAffaire($compte);

            if ($affaires == false) {
                $affaires = [];
            }
            
            $fromPage = $request->get('fromPage');
            $genre = $compte->getGenre();

            $session->set('compte', $compte->getId());
           
            if($genre == 1) {
                $data["html"] = $this->renderView('admin/affaires/index_client.html.twig', [
                    'listes' => $affaires,
                    'compte' => $compte,
                    'genre' => $genre,
                    'count' => count($affaires)
                ]);
            } elseif($genre == 2) {
                $data["html"] = $this->renderView('admin/affaires/index_fournisseur.html.twig', [
                    'listes' => $affaires,
                    'compte' => $compte,
                    'genre' => $genre,
                    'count' => count($affaires)
    
                ]);
            }

            $data['count'] = count($affaires);
            
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/from-facture/{compte}', name: '_liste_affaire_from_facture')]
    public function listeAffaireFromFacture(Compte $compte, Request $request, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        try {

            $affaires = $this->affaireService->getAllAffaire($compte);

            if ($affaires == false) {
                $affaires = [];
            }
            
            $fromPage = $request->get('fromPage');
            $genre = $compte->getGenre();

            $session->set('compte', $compte->getId());

            $scheme = $request->getScheme(); // 'http' ou 'https'
            $host = $request->getHost(); // exemple: 'www.example.com'
            $fullUrl = $scheme . '://' . $host;

            return $this->redirect($fullUrl."/admin#affaires_client");
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }
    
    #[Route('/search/{compte}', name: '_search')]
    public function search(Compte $compte, Request $request)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        $statut = $request->get('type');
        try {
            $affaires = $this->affaireService->getAllAffaire($compte,1,0, $statut);

            if ($affaires == false) {
                $affaires = [];
            }
           
            $data["html"] = $this->renderView('admin/affaires/list_affaire.html.twig', [
                'listes' => $affaires,
                'compte' => $compte,
                'count' => count($affaires)
               
            ]);
           
            $data['count'] = count($affaires);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/search-compte-datatable/from-ajax', name: '_search_ajax')]
    public function searchAjax(Request $request, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        $genre = $request->request->get('genre');
        $nomCompte = $request->request->get('nomCompte');
        $dateDu = $request->get('dateDu');
        $dateAu = $request->get('dateAu');
        $start = $request->get('start');
        $draw = $request->get('draw');
        $search = $request->get('search');
        $order = $request->get('order');
        $length = $request->get('length');
        try {
            if (null != $dateDu) {
                $dateDuExplode = explode("/", $dateDu);
                $dateDu = new \DateTime($dateDuExplode[2] . "-" . $dateDuExplode[1] . "-" . $dateDuExplode[0]);
            }
    
            $dateAu = $request->get('dateAu');
    
            if (null != $dateAu) {
                $dateAuExplode = explode("/", $dateAu);
                $dateAu = new \DateTime($dateAuExplode[2] . "-" . $dateAuExplode[1] . "-" . $dateAuExplode[0]);
            }

            if ($start == 0) {
                $nbCompte = $this->affaireService->searchCompteRawSql($genre, $nomCompte, $dateDu, $dateAu, null, $start, $length, null, true);
              
                $session->set('nbCompte_'.$genre, $nbCompte);
            } else {
                $nbCompte = $session->get('nbCompte_'.$genre);
            }

           $comptesAssoc = $this->affaireService->searchCompteRawSql($genre, $nomCompte, $dateDu, $dateAu, null, $start, $length, null, false);
        
           $data = [];

            if ($comptesAssoc) {
                $k = 0;
                foreach ($comptesAssoc as $compteArray) {
                    $compte = $this->affaireService->find($compteArray['id']);
                    $data[$k][] = $compte->getNom();
                    $data[$k][] = $compte->getAdresse();
                    $textEdit = "<ul class=\"list-unstyled action m-0\">
                            <li>
                        
                            <a onclick=\"return openModalUpdatecompte(" . $compte->getId() . ", " . $compte->getGenre() . ");\" class=\"\"><i class=\"bi bi-pencil-fill\"></i></a>
                        
                                <a class=\"text-danger\" href=\"#\" onclick=\"return deleteCompte(" . $compte->getId() . ", " . $compte->getGenre() . ");\"><span class=\"bi bi-trash text-danger\" aria-hidden=\"true\" ></span></a>
                            </li>
                        </ul>";

                $data[$k][] = $textEdit;
                $k++;
                }
            }
           
            return new JsonResponse([
                'draw' => $draw,
                "recordsTotal" => $nbCompte,
                "recordsFiltered" => $nbCompte,
                "data" => $data
            ]);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/new/{compte}', name: '_create')]
    public function create(Compte $compte, Request $request, UserPasswordHasherInterface $userPasswordHasher)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $statut = $request->get('statut');

            $affaire = new Affaire();

            $applications = $this->applicationRepo->findByApplication($this->application->getId());
            
            $form = $this->createForm(AffaireType::class, $affaire);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    // encode the plain password
                    $application = null;
                    $depot = null;

                    if($statut == "commande") {
                        $revendeur = $request->get('revendeur');
                        $depot = $request->get('depot');
                        $applicationRevendeurId = $request->get('application-commande');
    
                        if($revendeur == 'on') {
                            //$request->getSession()->set('applicationRevendeurId', $applicationRevendeurId);
                            $application = $this->applicationRepo->findOneBy(['id' => $applicationRevendeurId]);
                        } 
                    }

                    $this->affaireService->add($affaire, $statut, $compte, $application, $depot);

                    $this->affaireService->update();
                   
                    $affaires = $this->affaireService->getAllAffaire($compte);

                    if ($affaires == false) {
                        $affaires = [];
                    }

                    $data["html"] = $this->renderView('admin/affaires/list_affaire.html.twig', [
                        'compte' => $compte,
                        'listes' => $affaires,

                    ]);
                   
                    return new JsonResponse($data);
                    
                }

                $this->addFlash('success', 'Création affaire "' . $affaire->getNom() . '" avec succès.');
                return $this->redirectToRoute('comptes_liste', [
                    'genre' => 1
                ]);

                
            }

            $data["html"] = $this->renderView('admin/affaires/new_affaire.html.twig', [
                'form' => $form->createView(),
                'compte' => $compte,
                'statut' => $statut,
                'applications' => $applications
            ]);
           
            return new JsonResponse($data);
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
        return new JsonResponse($data);
    }

    #[Route('/edit/{affaire}', name: '_edit')]
    public function edit(Request $request, Affaire $affaire)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(AffaireType::class, $affaire, []);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                 
                   $this->affaireService->persist($affaire);
                    // Obtenir l'utilisateur connecté
                    $user = $this->getUser();

                    // Créer le message de log en fonction de l'action
                    $logMessage = ($affaire->getStatut() == 'devis') ? 'Devis modifié' : 'Commande modifiée';
            
                    // Créer le log
                    $this->logger->info($logMessage, [
                        'Commande' => $affaire->getNom(),
                        'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                        'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                        'ID Application' => $affaire->getApplication()->getId()
                    ]);
                    $this->affaireService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification utilisateur  "' . $utilisateur->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            $data['exception'] = "";
           
            $data["html"] = $this->renderView('admin/affaires/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $affaire->getId(),
            ]);
            
            return new JsonResponse($data);
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
        return new JsonResponse($data);
    }

    #[Route('/delete/{affaire}', name: '_delete')]
    public function delete(Request $request, Affaire $affaire)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->affaireService->remove($affaire);
                // Obtenir l'utilisateur connecté
                $user = $this->getUser();

                // Créer le message de log en fonction de l'action
                $logMessage = ($affaire->getStatut() == 'devis') ? 'Devis supprimé' : 'Commande supprimée';
        
                // Créer le log
                $this->logger->info($logMessage, [
                    'Commande' => $affaire->getNom(),
                    'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                    'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                    'ID Application' => $affaire->getApplication()->getId()
                ]);
                $this->affaireService->update();
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
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

    #[Route('/information/{id}', name: '_information')]
    public function info(
        Request $request, 
        Affaire $affaire,
        SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        try {

            $session->set('idAffaire', $affaire->getId());

            $compte = $affaire->getCompte();

            $data["html"] = $this->renderView('admin/affaires/information.html.twig', [
                'affaire' => $affaire,
                'id' => $affaire->getId(),
                'compte' => $compte
            ]);
            
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/produit/{compte}', name: '_liste_produit_by_fournisseur')]
    public function listeProduit(
        Compte $compte, 
        Request $request, 
        SessionInterface $session,
        ProduitCategorieService $produitCategorieService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/

        $session->set('idCompte', $compte->getId());
      
        $data = [];
        try {

            $produitCategories = $produitCategorieService->getAllProduitByCompteAndApplication($compte, $this->application);

            if ($produitCategories == false) {
                $produitCategories = [];
            }

            $data["html"] = $this->renderView('admin/affaires/index_fournisseur.html.twig', [
                'listes' => $produitCategories,
                'compte' => $compte
            ]);
            
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/financier/{affaire}', name: '_financier')]
    public function financier(Request $request, Affaire $affaire, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/

        $idFac = $request->getSession()->get('idFacture');
        $idFacture = null;
        if($idFac) {
            $idFacture = $idFac;
        }
      
        $data = [];
        try {
            $session->set('idAffaire', $affaire->getId());
            $produits = $this->productService->findProduitAffaire($affaire);
            if ($produits == false) {
                $produits = [];
            }
           // dd($produits[0]->getTypeVente(), $produits[0]->getQtt(), $produits[0]->getProduitCategorie()->getVolumeGros(), $produits[0]->getProduitCategorie()->getUniteVenteGros(), $produits[0]->getProduitCategorie()->getVolumeDetail(), $produits[0]->getProduitCategorie()->getUniteVenteDetail());
            $facturesValide = [];
            if ($affaire->getPaiement() != null && count($affaire->getFactures()) > 0) {
                $factures = $affaire->getFactures();
                if ($affaire->getPaiement() != "annule") {
                    $facturesValide = $factures->filter(function ($item) use ($affaire) {
                        return ($item->isValid() && ('regle' === $item->getStatut() || 'encours' === $item->getStatut() ));
                        
                    });
                } else {
                    $facturesValide = $factures->filter(function ($item) use ($affaire) {
                        return ($item->isValid() && 'annule' === $item->getStatut());
                    });
                }
               
            }
           
            $data["html"] = $this->renderView('admin/affaires/financier.html.twig', [
                'affaire' => $affaire,
                'produits' => $produits,
                'idFacture' => $idFacture,
                'factureFile' => ((count($facturesValide) > 0 && $facturesValide[count($facturesValide) - 1] != null) ? $facturesValide[count($facturesValide) - 1]->getFile(): null)
            ]);
          
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/financier/from-other-page/{affaire}', name: '_financier_from_other_page')]
    public function showFinancier(Request $request, Affaire $affaire, SessionInterface $session)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        try {
            $session->set('idAffaire', $affaire->getId());
            $scheme = $request->getScheme(); // 'http' ou 'https'
            $host = $request->getHost(); // exemple: 'www.example.com'
            $fullUrl = $scheme . '://' . $host;

            return $this->redirect($fullUrl."/admin#tab-financier-affaire");
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/produit/liste/{affaire}', name: '_liste_produit')]
    public function listProduits(
        Request $request,
        ProduitCategorieService $produitCategorieService,
        Affaire $affaire,
        CategorieService $categorieService)
    {
      
        $data = [];
        try {

            $categories = $categorieService->getAllCategories();
            $products = $affaire->getProducts();
            $tabIdProduitCategorieInAffaires = [];
            /*if (count($products) > 0) {
                foreach ($products as $key => $product) {
                    if (!in_array($product->getProduitCategorie()->getId(), $tabIdProduitCategorieInAffaires)) {
                        array_push($tabIdProduitCategorieInAffaires,$product->getProduitCategorie()->getId());
                    }
                }
            }*/
            $produitCategories = $produitCategorieService->getAllProduitCategoriesByStockRestant($tabIdProduitCategorieInAffaires);
          
            $data["html"] = $this->renderView('admin/affaires/liste_produit.html.twig', [
                'listes' => $produitCategories,
                'affaire' => $affaire,
                'compte' => $affaire->getCompte(),
                'categories' => $categories
            ]);
                
            return new JsonResponse($data);
           
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/fiche/{compte}', name: '_fiche')]
    public function ficheClient(
        Request $request, 
        Compte $compte,
        SessionInterface $session,
        EntityManagerInterface $em)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/

        $affaires = $compte->getAffaires();

        $session->set('idCompte', $compte->getId());
      
        $data = [];
        try {

            $form = $this->createForm(FicheCompteType::class, $compte, []);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                 
                   $em->persist($compte);
                    $em->flush();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification utilisateur  "' . $utilisateur->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            if($compte->getGenre() == 1)
            {
                $data["html"] = $this->renderView('admin/comptes/fiche_client.html.twig', [
                    'compte' => $compte,
                    //'affaire' => $affaire,
                    'form' => $form->createView()
                ]);
            } elseif($compte->getGenre() == 2) {
                $data["html"] = $this->renderView('admin/comptes/fiche_fournisseur.html.twig', [
                    'compte' => $compte,
                    //'affaire' => $affaire,
                    'form' => $form->createView()
                ]);
            }

            
            
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/facture/{affaire}', name: '_facture')]
    public function facture(SessionInterface $session, Affaire $affaire): Response
    {
        $session->set('idAffaire', $affaire->getId());
        $session->set('idCompte', $affaire->getCompte()->getId());

        $factures = $this->factureService->getAllFacturesByAffaire($affaire->getId());

        $factureEcheanceId = null;
        foreach($factures as $facture) {
            $factureEcheances = explode(',', $facture['factureEcheances']); 
            $countFactureEcheances = count($factureEcheances); 
        
            if ($countFactureEcheances > 0) {
                $factureEcheanceId = $factureEcheances[$countFactureEcheances - 1]; 
            }
        }
        
        $factureEcheance = $this->factureEcheanceRepo->findOneBy(['id' => $factureEcheanceId]);

        $data = [];
        try {
            
            $data["html"] = $this->renderView('admin/affaires/facture.html.twig', [
                'affaire' => $affaire,
                'factures' => $factures,
                'factureEcheance' => $factureEcheance,
                'application' => $this->application
            ]);
           
            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
        
    }

    #[Route('/paiement/{affaire}', name: '_paiement')]
    public function payer(Affaire $affaire, Request $request): Response
    {
        $applicationRevendeur = $affaire->getApplicationRevendeur();

        if (count($affaire->getProducts()) > 0) {
            $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/valide/';

            // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
            if (!is_dir($documentFolder)) {
                mkdir($documentFolder, 0777, true); // 0777 pour les permissions, et `true` pour créer récursivement les sous-dossiers
            }
            
            list($pdfContent, $facture) = $this->factureService->add($affaire, $documentFolder, $request, $applicationRevendeur);
            
            $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
            $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/valide/' . $filename;
            
            // Sauvegarder le fichier PDF
            file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
            
            $request->getSession()->set('idFacture', $facture->getId());

            return new JsonResponse([
                'status' => 'success',
                'pdfUrl' => $pdfPath,
            ]);
            
        }
        
        return new JsonResponse([]);
    }

    #[Route('/paiement/annule/{affaire}', name: '_annuler')]
    public function annulerPaiement(Affaire $affaire, Request $request): Response
    {
        /*if (count($affaire->getProducts()) > 0) {
            $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/factures/annule/';
            $pdf = $this->factureService->annuler($affaire, $documentFolder);
           
            return new Response($pdf->Output('test.pdf', 'I'), 200, [
                'Content-Type' => 'application/pdf',
            ]);
    
        }
        return new JsonResponse([]);*/
       
        if (count($affaire->getProducts()) > 0) {
            $documentFolder = $this->getParameter('kernel.project_dir') . '/public/uploads/APP_'.$this->application->getId().'/factures/annule/';
           
            if (!is_dir($documentFolder)) {
                if (!mkdir($documentFolder, 0775, true)) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le dossier des annulations ne peut pas être créé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            list($pdfContent, $facture) = $this->factureService->annuler($affaire, $documentFolder, $request);
            
            // Utiliser le numéro de la facture pour le nom du fichier
            //$filename = "Facture(FA-Annuler-" . $facture->getNumero() . ").pdf";
            $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
            $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/annule/' . $filename;
            file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
            
            // Retourner le PDF en réponse
            /*return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);*/
            return new JsonResponse([
                'status' => 'success',
                'pdfUrl' => $pdfPath,
            ]);
        }
        
        return new JsonResponse([]);
    }

    #[Route('/depot/{affaire}', name: '_depot')]
    public function depot(Request $request, Affaire $affaire)
    {
        $request->getSession()->set('idAffaire', $affaire->getId());

        $data = [];
        try {

                $data["html"] = $this->renderView('admin/affaires/depot.html.twig', [
                    'affaire' => $affaire,
                ]);

            return new JsonResponse($data);
        } catch (\Exception $Exception) {
            $data["exception"] = $Exception->getMessage();
            $data["html"] = "";
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/depot/add/{affaire}', name: '_depot_add')]
    public function addDepot(Request $request, Affaire $affaire)
    {

        if (count($affaire->getProducts()) > 0) {
            $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/valide/';

            // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
            if (!is_dir($documentFolder)) {
                mkdir($documentFolder, 0777, true); // 0777 pour les permissions, et `true` pour créer récursivement les sous-dossiers
            }
            
            list($pdfContent, $facture) = $this->factureService->addDepot($affaire, $documentFolder, $request);
            
            $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
            $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/valide/' . $filename;
            
            // Sauvegarder le fichier PDF
            file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
            
            return new JsonResponse([
                'status' => 'success',
                'pdfUrl' => $pdfPath,
            ]);
            
        }
        
        return new JsonResponse([]);
    }

    #[Route('/depot/valid/{product}', name: '_depot_valid')]
    public function productDepot(Request $request, Product $product)
    {
        $idAffaire = $request->getSession()->get('idAffaire');
        $affaire = $this->affaireRepo->findOneBy(['id' => $idAffaire]);

        $form = $this->createForm(ProductDepotType::class, null);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $formData = $form->getData();
                $qttVendu = $formData['qttVendu'];
                
                if ($request->isXmlHttpRequest()) {
                    
                    $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/factures/valide/';

                    // Vérifier si le dossier existe, sinon le créer avec les permissions appropriées
                    if (!is_dir($documentFolder)) {
                        mkdir($documentFolder, 0777, true); // 0777 pour les permissions, et `true` pour créer récursivement les sous-dossiers
                    }
                    
                    list($pdfContent, $facture) = $this->factureService->validDepot($affaire, $documentFolder, $request, $product, $qttVendu);
                    
                    $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";

                    $pdfPath = '/uploads/APP_'.$this->application->getId().'/factures/valide/' . $filename;
                    
                    // Sauvegarder le fichier PDF
                    file_put_contents($this->getParameter('kernel.project_dir') . '/public' . $pdfPath, $pdfContent);
                    
                    return new JsonResponse([
                        'status' => 'success',
                        'pdfUrl' => $pdfPath,
                    ]);

                }

            }


            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/affaires/modal_depot.html.twig', [
                'form' => $form->createView(),
                'product' => $product,
                'affaire' => $affaire
            ]);
           
            return new JsonResponse($data);

        } catch (PropertyVideException $PropertyVideException) {
            throw $this->createNotFoundException('Exception' . $PropertyVideException->getMessage());
        } 

        return new JsonResponse($data);
    }
     /**
     * @param Request $request
     * @param ProduitRepository $produitRepository
     * @param AffaireRepository $affaireRepository
     * @Route("/financiere/addRemise/ajax", name="add_remise_product_ajax", methods={"POST"})
     * @return Response
     */
    public function addRemise(
        Request               $request,
        ProductRepository     $productRepository,
        AffaireRepository     $affaireRepository
    )
    {
        $idProduit = $request->get('idProduit');

        $idAffaire = $request->get('idAffaire');
        
        $type = $request->get('type');

        $isFrais = $request->get('isFrais');

        $option = $request->get('option');

        $uri = $request->get('uri');

        $produit = [];

        $affaire = [];

        $montantRemise = "";

        $montantPourcent = "";

        switch ($type) {
            case "produit":
                $produit = $productRepository->find($idProduit);

                $montantRemise = $produit->getRemise();

                //$montantTotal = $produit->getPuHt()*$produit->getQtt() - $montantRemise;
                $montantTotal = $produit->getPrixVenteDetail() * $produit->getQtt();
                if ($produit->getTypeVente() == "gros") {
                    $montantTotal = $produit->getPrixVenteGros() * $produit->getQtt();
                }
                
                $montantPourcent = $produit->getRemisePourcent();

                break;

            case "affaire":
                $affaire = $affaireRepository->find($idAffaire);

                $montantRemise = $affaire->getRemise();

                //$montantTotal = $affaire->getCa() - $affaire->getRemiseProduit() - $montantRemise;

                $produits = $affaire->getProducts();

                
                $applicationId = $this->application->getId();

                $produits = $produits->filter(function ($item) use ($applicationId) {
                    return $item->getApplication()->getId() == $applicationId;
                });

                $montantTotal = 0;

                foreach ($produits as $unProduit) {
                    if ($unProduit->getTypeReduction()) {
                        if ($unProduit->getTypeReduction() == "remise" && null == $isFrais) {
                            $montantTotal = $montantTotal + $unProduit->getPuHt() * $unProduit->getQtt() - $unProduit->getRemise();
                        }
                    } else
                        $montantTotal = $montantTotal + $unProduit->getPuHt() * $unProduit->getQtt() - $unProduit->getRemise();
                }

                //$montantTotal = $affaire->getCa() + $montantRemise;

                $montantPourcent = $affaire->getRemisePourcent();

                break;
        }

        //$tvaVentes = ($type == "affaire") ? $comptableRepository->findBy(['typeCompte' => 'tva_vente', 'application' => $this->application], ['tva' => 'ASC']) : "";

        //$template = (null != $option) ? "modalRetenue.html.twig" : "modalRemise.html.twig";

        /*if (null != $isFrais && $isFrais == "true") {
            $template = "modalFraisTechnique.html.twig";
        }*/

        return $this->render('admin/affaires/modalRemise.html.twig', [
            'produit' => $produit,
            'affaire' => $affaire,
            'type' => $type,
            'montantTotal' => $montantTotal,
            'uri' => $uri,
            'montantRemise' => $montantRemise,
            'montantPourcent' => $montantPourcent,
            'tvaVentes' => [],
            'option' => $option,
            'isFrais' => $isFrais,
            'idAffaire' => $idAffaire
        ]);
    }

    /**
     * @param Request $request
     * @param ProduitRepository $produitRepository
     * @param AffaireRepository $affaireRepository
     * @Route("/financiere/valideAddRemise/ajax", name="_valide_add_remise_product_ajax", methods={"POST"})
     * @return Response
     */
    public function valideAddRemise(
        Request                       $request,
        ProductRepository             $productRepository,
        AffaireRepository             $affaireRepository,
        ProductService                $productService,
        ApplicationRepository         $applicationRepository,
        ApplicationManager            $applicationManager
    )
    {
        $idProduit = $request->get('idProduit');

        $idAffaire = $request->get('idAffaire');

        $applicationCurrentInSession = $this->application;

        $type = $request->get('type');

        $montantRemise = $request->get('montantRemise');

        $montantRemiseFinale = $request->get('montantRemiseFinale');
     
        $typeRemise = $request->get('typeremise');

        $uri = $request->get('uri');

        $typeReduction = $request->get('typeReduction');

        $produit = [];

        $affaire = [];

        switch ($type) {
            case "produit":
                $produit = $productRepository->find($idProduit);
                
                $produit->setRemise($montantRemiseFinale);

                $affaires = $produit->getAffaires();

                $affaire = $affaires[0];

                $remiseAffaire = $affaire->getRemiseProduit();

                if (null != $remiseAffaire) {
                } else {
                    $remiseAffaire = 0;
                }

                $remiseProduit = $montantRemiseFinale + $remiseAffaire;

                $affaire->setRemiseProduit($montantRemiseFinale);

                if ($typeRemise == "1") {
                    $produit->setRemisePourcent($request->get('montantRemise'));
                } else {
                    $produit->setRemisePourcent(null);
                }

                if (is_null($produit->getApplication())) {
                    $produit->setApplication($this->application);
                }

                $productService->persist($produit);

                $productService->persist($affaire);

                $montantRemiseFinale = 0;

                break;

            case "affaire":
                $affaire = $affaireRepository->find($idAffaire);

                $montantRemise = $affaire->getRemise();


                /*$affaire->setRemise($montantRemiseFinale);

                if ($typeRemise == "1") {
                    $affaire->setRemisePourcent($request->get('montantRemise'));
                } else {
                    $affaire->setRemisePourcent(null);
                }

                $entityManager->persist($affaire);*/

                $tva = $request->get('tva');

                $titreRemise = (null != $request->get('titreRemise')) ? $request->get('titreRemise') : "Remise globale";

                $idProduit = $request->get('id');

                if (null != $idProduit) {
                    $produit = $productRepository->find($idProduit);
                } else {
                    $produit = new Product();
                    $produit->addAffaire($affaire);
                }

                if ($montantRemiseFinale < 0) {
                    $montantRemiseFinale = $montantRemiseFinale * -1;
                }

                //$affaire->setRemise($montantRemise + $montantRemiseFinale);
                if ($typeReduction == "frais") {
                    $montantRemiseFinale = $montantRemiseFinale * -1;
                }

                $produit->setNom($titreRemise)
                    ->setQtt(1)
                    ->setPuHt($montantRemiseFinale * -1)
                    ->setTva($tva);

                if (is_null($produit->getApplication())) {
                    $produit->setApplication($this->application);
                }

                if ($typeRemise == "1") {
                    $produit->setRemisePourcent($request->get('montantRemise'));
                } else {
                    $produit->setRemisePourcent(null);
                }

                $typeReduction = (null != $request->get('typeReduction')) ? $request->get('typeReduction') : "remise";

                $produit->setTypeReduction($typeReduction);


                $productService->persist($produit);
                $productService->persist($affaire);


                break;
        }

        $productService->update();


        //$montantCA = $productService->updateCA($affaire, $productRepository);

        $produits = $this->productService->findProduitAffaire($affaire);

        $tabCleProduit = [];

        $facturesValide = [];
        if ($affaire->getPaiement() != null && count($affaire->getFactures()) > 0) {
            $factures = $affaire->getFactures();
            $facturesValide = $factures->filter(function ($item) use ($affaire) {
                return ($item->isValid() && 'regle' === $item->getStatut());
                
            });
        }

        $idFac = $request->getSession()->get('idFacture');
        $idFacture = null;
        if($idFac) {
            $idFacture = $idFac;
        }

        return $this->render("admin/affaires/reloadFinanciereProduct.html.twig", [
            'idApplication' => $this->application->getId(),
            'affaire' => $affaire,
            'produits' => $produits,
            'montantCA' =>  null, // $montantCA - $montantRemiseFinale,
            'affaireRemise' => $montantRemiseFinale,
            'tvaVentes' => [],
            'statuts' => $affaire::STATUT,
            'application' => $this->application,
            'idFacture' => $idFacture,
            'factureFile' => (count($facturesValide) > 0 ? $facturesValide[count($facturesValide) - 1]->getFile(): null)

        ]);

        //$this->addFlash('success', 'L\' enregistrement est bien effectuée avec succès!');

        //return $this->redirect($uri);
    }

    /**
     * @param Request $request
     * @param ProduitRepository $produitRepository
     * @param AffaireRepository $affaireRepository
     * @Route("/financiere/deleteAddRemise/ajax", name="_delete_add_remise_product_ajax", methods={"POST"})
     * @return Response
     */
    public function deleteAddRemise(Request $request, ProductRepository   $productRepository, AffaireRepository $affaireRepository, ProductService   $productService)
    {
        $idProduit = $request->get('idProduit');

        $idAffaire = $request->get('idAffaire');

        $type = $request->get('type');

        $montantRemiseFinale = $request->get('montantRemiseFinale');

        $uri = $request->get('uri');

        $produit = [];

        $affaire = [];
        switch ($type) {
            case "produit":
                $produit = $productRepository->find($idProduit);

                $affaires = $produit->getAffaires();

                $affaire = $affaires[0];

                $remiseAffaire = $affaire->getRemiseProduit();

                if (null != $remiseAffaire) {
                } else {
                    $remiseAffaire = 0;
                }

                $affaire->setRemiseProduit($remiseAffaire - $produit->getRemise());

                $produit->setRemise(null);

                $produit->setRemisePourcent(null);

                $productService->persist($produit);

                $productService->persist($affaire);

                break;

            case "affaire":
                $affaire = $affaireRepository->find($idAffaire);

                $affaire->setRemise(null);

                $affaire->setRemisePourcent(null);

                $productService->persist($affaire);

                break;
        }

        $productService->update();

       // $montantCA = $produitService->updateCA($affaire, $produitRepository);

       $produits = $this->productService->findProduitAffaire($affaire);

       $facturesValide = [];
       if ($affaire->getPaiement() != null && count($affaire->getFactures()) > 0) {
           $factures = $affaire->getFactures();
           $facturesValide = $factures->filter(function ($item) use ($affaire) {
               return ($item->isValid() && 'regle' === $item->getStatut());
               
           });
       }

       $idFac = $request->getSession()->get('idFacture');
        $idFacture = null;
        if($idFac) {
            $idFacture = $idFac;
        }

       return $this->render("admin/affaires/reloadFinanciereProduct.html.twig", [
           'idApplication' => $this->application->getId(),
           'affaire' => $affaire,
           'produits' => $produits,
           'montantCA' =>  null, // $montantCA - $montantRemiseFinale,
           'affaireRemise' => $montantRemiseFinale,
           'tvaVentes' => [],
           'idFacture' => $idFacture,
           'statuts' => $affaire::STATUT,
           'application' => $this->application,
           'factureFile' => (count($facturesValide) > 0 ? $facturesValide[count($facturesValide) - 1]->getFile(): null)

       ]);
    }


    #[Route('/valider/{affaire}', name: '_valider')]
    public function valider(Affaire $affaire, Request $request, UserInterface $user): Response
    {
        $request->getSession()->set('idAffaire', $affaire->getId());
        
        if (count($affaire->getProducts()) > 0) {
            $affaire->setValid(true);
            $affaire->setDateValidation(new \DateTime());
            $this->em->persist($affaire);
            $this->em->flush();

            $formattedDate = $this->dateFormatter->formatDate($affaire->getDateValidation());

            $newAffaires = $this->affaireRepo->findBy(['paiement' => 'non', 'isValid' => true, 'application' => $this->application]);
            $countAffaires = count($newAffaires);
    
            return new JsonResponse([
                'status' => 'success',
                'affaireId' => $affaire->getId(),
                'affaireName' => $affaire->getNom(),
                'dateValidation' => $formattedDate,
                'countAffaires' => $countAffaires,
                'newAffaires' => $newAffaires,
            ]);
        }
        
        return new JsonResponse([]);
    }
    
    #[Route('/verifier/new/affaire', name: '_verifier_new_affaire')]
    public function getNewAffaires(): JsonResponse
    {
        $newAffaires = $this->affaireRepo->findBy(['paiement' => 'non', 'isValid' => true, 'application' => $this->application]);
        $countAffaires = count($newAffaires);

        // Préparer les données à retourner
        $affairesData = [];
        foreach ($newAffaires as $affaire) {
            $formattedDate = $this->dateFormatter->formatDate($affaire->getDateValidation());

            $affairesData[] = [
                'id' => $affaire->getId(),
                'nom' => $affaire->getNom(), 
                'dateValidation' => $formattedDate,
                'countAffaires' => $countAffaires,
                'newAffaires' => $newAffaires,
            ];
        }

        // Retourner les affaires validées
        return new JsonResponse(['affaires' => $affairesData]);
    }


    
}
