<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Compte;
use App\Form\UserType;
use App\Entity\Affaire;
use App\Entity\Facture;
use App\Form\CompteType;
use App\Form\ProfilType;
use App\Form\AffaireType;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Form\FicheCompteType;
use App\Service\AccesService;

use App\Service\CompteService;
use App\Form\AccesExtranetType;
use App\Service\AffaireService;
use App\Service\FactureService;
use App\Service\ProductService;
use App\Service\CategorieService;
use App\Service\ApplicationManager;
use App\Repository\CompteRepository;
use App\Exception\PropertyVideException;
use App\Service\ProduitCategorieService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\UnsufficientPrivilegeException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
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


    public function __construct(
        AffaireService $affaireService, 
        ApplicationManager $applicationManager, 
        AccesService $accesService, 
        ProductService $productService, 
        FactureService $factureService,
        LoggerInterface $affaireLogger, 
        
        )
    {
        $this->affaireService = $affaireService;
        $this->accesService = $accesService;
        $this->productService = $productService;
        $this->application = $applicationManager->getApplicationActive();
        $this->factureService = $factureService;
        $this->logger = $affaireLogger;

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

            return $this->redirect("http://www.gestion-commerciale.com/admin#affaires_client");
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
            
            $form = $this->createForm(AffaireType::class, $affaire);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    // encode the plain password
                    $this->affaireService->add($affaire, $statut, $compte);
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
                'statut' => $statut
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
                        'Produit' => $affaire->getNom(),
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
                    'Produit' => $affaire->getNom(),
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
                        return ($item->isValid() && 'regle' === $item->getStatut());
                        
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
            return $this->redirect("http://www.gestion-commerciale.com/admin#tab-financier-affaire");
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
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        
        $data = [];
        try {

            $categories = $categorieService->getAllCategories();
            $products = $affaire->getProducts();
            $tabIdProduitCategorieInAffaires = [];
            if (count($products) > 0) {
                foreach ($products as $key => $product) {
                    if (!in_array($product->getProduitCategorie()->getId(), $tabIdProduitCategorieInAffaires)) {
                        array_push($tabIdProduitCategorieInAffaires,$product->getProduitCategorie()->getId());
                    }
                }
            }
            $produitCategories = $produitCategorieService->getAllProduitCategories($tabIdProduitCategorieInAffaires);
           
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

        $factures = $this->factureService->getAllFacturesByAffaire($affaire->getId());

        $data = [];
        try {
            
            $data["html"] = $this->renderView('admin/affaires/facture.html.twig', [
                'affaire' => $affaire,
                'factures' => $factures
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
        /*if (count($affaire->getProducts()) > 0) {
            $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/factures/valide/';
           
            $pdf = $this->factureService->add($affaire, $documentFolder);
           
            return new Response($pdf->Output('test.pdf', 'I'), 200, [
                'Content-Type' => 'application/pdf',
            ]);
    
        }
        return new JsonResponse([]);*/

        if (count($affaire->getProducts()) > 0) {
            $documentFolder = $this->getParameter('kernel.project_dir'). '/public/uploads/factures/valide/';
            list($pdfContent, $facture) = $this->factureService->add($affaire, $documentFolder, $request);
            
            // Utiliser le numéro de la facture pour le nom du fichier
            //$filename = "Facture(FA-" . $facture->getNumero() . ").pdf";
            $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
            
            // Retourner le PDF en réponse
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
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
            $documentFolder = $this->getParameter('kernel.project_dir') . '/public/uploads/factures/annule/';
           

            list($pdfContent, $facture) = $this->factureService->annuler($affaire, $documentFolder);
            
            // Utiliser le numéro de la facture pour le nom du fichier
            //$filename = "Facture(FA-Annuler-" . $facture->getNumero() . ").pdf";
            $filename = $affaire->getCompte()->getIndiceFacture() . '-' . $facture->getNumero() . ".pdf";
        
            // Retourner le PDF en réponse
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]);
        }
        
        return new JsonResponse([]);
    }
}
