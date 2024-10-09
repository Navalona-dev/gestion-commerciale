<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ProfilType;
use App\Service\AccesService;
use App\Service\ApplicationManager;
use App\Service\UserService;
use App\Form\AccesExtranetType;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/utilisateurs', name: 'utilisateurs')]
class UserController extends AbstractController
{
    private $userService;
    private $accesService;
    private $application;

    public function __construct(UserService $UserService, ApplicationManager $applicationManager, AccesService $accesService)
    {
        $this->userService = $UserService;
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
    }

    #[Route('/', name: '_liste')]
    public function index()
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
      
        $data = [];
        try {
            
            $utilisateurs = $this->userService->getAllUser();

            if ($utilisateurs == false) {
                $utilisateurs = [];
            }
            $data["html"] = $this->renderView('admin/utilisateurs/index.html.twig', [
                'listes' => $utilisateurs,
            ]);
            
            return new JsonResponse($data);
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
            $user = new User();
            $form = $this->createForm(UserType::class, $user);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    // encode the plain password
                
                    $this->userService->add($user, $userPasswordHasher);
                    $this->userService->update();
                   
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
        
                //$this->addFlash('success', 'Création privilege "' . $user->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/utilisateurs/new.html.twig', [
                'form' => $form->createView(),
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

    #[Route('/{utilisateur}', name: '_edit')]
    public function edit(Request $request, User $utilisateur)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(UserType::class, $utilisateur, ['isEdit' => true]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    if (count($utilisateur->getApplications()) > 0) {
                        if (null == $utilisateur->getAppActive()) {
                            $utilisateur->setAppActive($utilisateur->getApplications()[0]);
                        }
                        
                        foreach ($utilisateur->getApplications() as $key => $application) {
                            $application->addUser($utilisateur);
                            $this->userService->persist($application);
                        }
                    }
                   //dd($utilisateur->getAppActive(), count($utilisateur->getApplications()));
                   
                   $this->userService->persist($utilisateur);
                    $this->userService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification utilisateur  "' . $utilisateur->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/utilisateurs/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $utilisateur->getId(),
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

    #[Route('/delete/{utilisateur}', name: '_delete')]
    public function delete(Request $request, User $utilisateur)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->userService->remove($utilisateur);
                $this->userService->update();
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

    #[Route('/profile/user', name: '_profile')]
    public function displayTabProfil(
        Request                 $request,
        UserPasswordHasherInterface $passwordEncoder,
        TokenStorageInterface $tokenStorage,
        ValidatorInterface $validator
    ): Response
    {
       
        $data = [];
        try {
           
            $user = $tokenStorage->getToken()->getUser();
            
            //$user = $this->userService->findUserById($currentUser->getId());
            $form = $this->createForm(ProfilType::class, $user);
          
            $formAcces = $this->createForm(AccesExtranetType::class, $user);
            $form->handleRequest($request);
            $formAcces->handleRequest($request);
          
            if ($form->isSubmitted() && $form->isValid()) {

                $directoryPublicCopy = $this->getParameter('kernel.project_dir'). '/public/uploads/APP_'.$this->application->getId().'/avatar/';
               
                if ($request->isXmlHttpRequest()) {
                   
                    $this->userService->update();
                    $data["html"] = $this->renderView('admin/profile/profile.html.twig', [
                        'profil' => $user,
                        'form' => $form->createView(),
                        'formAcces' => $formAcces->createView(),
                        'utilisateurId' => $user->getId()
                    ]);
                    return new JsonResponse($data);
                }
                //$this->userService->update();
                //$this->addFlash('success', 'Votre profil a été enregistré avec succès !');
    
                //return $this->redirect('/admin/utilisateurs/profile/user/#tab-profile');
            } 
            
            if ($formAcces->isSubmitted() && $formAcces->isValid()) {
                if($request->request->get('acces_extranet_new_password') != "" && $request->request->get('acces_extranet_new_password') != null) {
                    $user->setPassword(
                        $passwordEncoder->hashPassword(
                            $user,
                            $request->request->get('acces_extranet_new_password')
                        )
                    );
               }
               if ($request->isXmlHttpRequest()) {
                $this->userService->update();
                $data["html"] = $this->renderView('admin/profile/profile.html.twig', [
                    'profil' => $user,
                    'form' => $form->createView(),
                    'formAcces' => $formAcces->createView(),
                    'utilisateurId' => $user->getId()
                ]);
                return new JsonResponse($data);
            }
               //$this->userService->update();
               //$this->addFlash('success', 'Votre profil a été enregistré avec succès !');
    
               // return $this->redirect('/admin/utilisateurs/profile/user/#tab-profile');
            }
    
            $data["html"] = $this->renderView('admin/profile/profile.html.twig', [
                'profil' => $user,
                'form' => $form->createView(),
                'formAcces' => $formAcces->createView(),
                'utilisateurId' => $user->getId(),
                'application' => $this->application
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

    #[Route("/update-is-active/{id}", name:"app_is_active_user")]
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
    }

    /**
     * @Route("/profile", name="_profile")
     */
   /* public function profile(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user, ['isProfile' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('password')->getData()) {
                $user->setPassword($passwordEncoder->encodePassword($user, $form->get('password')->getData()));
            }
            $this->userService->update();
            $request->getSession()->getFlashBag()->add("success", "Votre profile a été modifié");
      
            return $this->render('user/profile.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
            ]);
        }
        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }*/
}
