<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Compte;
use App\Form\UserType;
use App\Form\CompteType;
use App\Form\ProfilType;
use App\Service\AccesService;
use App\Service\CompteService;
use App\Form\AccesExtranetType;
use App\Service\ApplicationManager;
use App\Exception\PropertyVideException;

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
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/comptes', name: 'comptes')]
class CompteController extends AbstractController
{
    private $compteService;
    private $accesService;
    private $application;

    public function __construct(CompteService $compteService, ApplicationManager $applicationManager, AccesService $accesService)
    {
        $this->compteService = $compteService;
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
    }

    #[Route('/', name: '_liste')]
    public function index(Request $request)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        $genre = $request->request->get('genre');
        try {
            $comptes = $this->compteService->getAllCompte((int)$genre);

            if ($comptes == false) {
                $comptes = [];
            }
            $data["html"] = $this->renderView('admin/comptes/index.html.twig', [
                'listes' => $comptes,
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
    
    #[Route('/search', name: '_search')]
    public function search(Request $request)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        $genre = $request->request->get('genre');
        $nomCompte = $request->request->get('nomCompte');
        try {
            $comptes = $this->compteService->getAllCompte((int)$genre);

            if ($comptes == false) {
                $comptes = [];
            }
            $data["html"] = $this->renderView('admin/comptes/index_ajax.html.twig', [
                'listes' => $comptes,
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
                $nbCompte = $this->compteService->searchCompteRawSql($genre, $nomCompte, $dateDu, $dateAu, null, $start, $length, null, true);
              
                $session->set('nbCompte_'.$genre, $nbCompte);
            } else {
                $nbCompte = $session->get('nbCompte_'.$genre);
            }

           $comptesAssoc = $this->compteService->searchCompteRawSql($genre, $nomCompte, $dateDu, $dateAu, null, $start, $length, null, false);
        
           $data = [];

            if ($comptesAssoc) {
                $k = 0;
                foreach ($comptesAssoc as $compteArray) {
                    $compte = $this->compteService->find($compteArray['id']);
                    $data[$k][] = "<a href=\"" . $this->generateUrl('affaires_liste', ['compte' => $compte->getId()]) . "\">" . $compte->getNom() . "</a>";
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

    #[Route('/new', name: '_create')]
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $compte = new Compte();
            $genre = $request->request->get('genre');

            $form = $this->createForm(CompteType::class, $compte, ['genre' => $genre]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    // encode the plain password
                    $this->compteService->add($compte,(integer) $genre);
                    $this->compteService->update();
                   
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }

                if($genre == 1) {
                    $this->addFlash('success', 'Création client "' . $compte->getNom() . '" avec succès.');
                    return $this->redirectToRoute('comptes_liste', [
                        'genre' => 1
                    ]);

                } elseif($genre == 2) {
                $this->addFlash('success', 'Création fournisseur "' . $compte->getNom() . '" avec succès.');
                    return $this->redirectToRoute('comptes_liste', [
                        'genre' => 2
                    ]);

                }
        
                //$this->addFlash('success', 'Création privilege "' . $user->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            $data['exception'] = "";

            if($genre == 1) {
                $data["html"] = $this->renderView('admin/comptes/new_client.html.twig', [
                    'form' => $form->createView(),
                    'genre' => $genre
                ]);
            } elseif($genre == 2) {
                $data["html"] = $this->renderView('admin/comptes/new_fournisseur.html.twig', [
                    'form' => $form->createView(),
                    'genre' => $genre
                ]);
            }
           
           
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

    #[Route('/{compte}', name: '_edit')]
    public function edit(Request $request, Compte $compte)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $genre = $compte->getGenre();

            $form = $this->createForm(CompteType::class, $compte, ['genre' => $genre]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                 
                   $this->compteService->persist($compte);
                    $this->compteService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification utilisateur  "' . $utilisateur->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            $data['exception'] = "";
            switch ($compte->getGenre()) {
                case 1:
                    $data["html"] = $this->renderView('admin/comptes/modal_update.html.twig', [
                        'form' => $form->createView(),
                        'id' => $compte->getId(),
                    ]);
                    break;
                case 2:
                    $data["html"] = $this->renderView('admin/comptes/modal_update_fournisseur.html.twig', [
                        'form' => $form->createView(),
                        'id' => $compte->getId(),
                    ]);
                    break;
                default:
                    # code...
                    break;
            }
            
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

    #[Route('/delete/{compte}', name: '_delete')]
    public function delete(Request $request, Compte $compte)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->compteService->remove($compte);
                $this->compteService->update();
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

    /*#[Route("/update-is-active/{id}", name:"app_is_active_user")]
    public function updateIsActive(Request $request, EntityManagerInterface $em, User $user): JsonResponse
    {
        if (!$user) {
            throw $this->createNotFoundException('Aucune entité trouvée pour l\'identifiant. '.$id);
        }

        $user->setIsActive(!$user->getIsActive());
        $em->persist($user);
        $em->flush();

        // Renvoyer une réponse JSON avec l'état mis à jour
        return new JsonResponse(['isActive' => $user->getIsActive()]);
    }*/


}
