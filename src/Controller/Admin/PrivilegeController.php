<?php

namespace App\Controller\Admin;

use App\Entity\Privilege;
use App\Service\AccesService;
use App\Form\PrivilegeBasicType;
use App\Service\PrivilegeService;
use App\Service\PermissionService;
use App\Service\ApplicationManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use App\Repository\PermissionRepository;
use App\Service\CategoryPermissionService;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\PrivilegeAssignationPermissionType;
use App\Exception\UnsufficientPrivilegeException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\Exception\ServerException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/privileges', name: 'privileges')]
class PrivilegeController extends AbstractController
{
    private $privilegeService;
    private $accesService;
    private $application;
    private $permissionRepo;

    public function __construct(
        PrivilegeService $privilegeService, 
        ApplicationManager $applicationManager, 
        AccesService $accesService,
        PermissionRepository $permissionRepo
        )
    {
        $this->privilegeService = $privilegeService;
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
        $this->permissionRepo = $permissionRepo;
    }

    #[Route('/', name: '_liste')]
    public function liste()
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {

            $privileges = $this->privilegeService->getAllPrivilege();

            if ($privileges == false) {
                $privileges = [];
            }
            $data["html"] = $this->renderView('admin/privileges/index.html.twig', [
                'listes' => $privileges,
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
    public function create(Request $request)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $privilege = new Privilege();
            $form = $this->createForm(PrivilegeBasicType::class, $privilege);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $this->privilegeService->add($privilege);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
        
                //$this->addFlash('success', 'Création privilege "' . $privilege->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/privileges/new.html.twig', [
                'form' => $form->createView(),
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
        }catch (NotNullConstraintViolationException $NotNullConstraintViolationException) {
            $this->createNotFoundException('Exception' . $NotNullConstraintViolationException->getMessage());
        } catch (\Exception $Exception) {
            $data['exception'] = $Exception->getMessage();
            $data["html"] = "";
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
        return new JsonResponse($data);
    }

    #[Route('/{privilege}', name: '_edit')]
    public function edit(Request $request, Privilege $privilege)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(PrivilegeBasicType::class, $privilege, ['isEdit' => true]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $this->privilegeService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification privilege  "' . $privilege->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('privilege_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/privileges/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $privilege->getId(),
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

    #[Route('/delete/{privilege}', name: '_delete')]
    public function delete(Request $request, Privilege $privilege)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->privilegeService->remove($privilege);
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
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

    #[Route('/assignation/permission/{privilege}', name: '_assignation_permission')]
    public function assignation(Request $request, CategoryPermissionService $CategoryPermissionService, PermissionService $PermissionService, Privilege $privilege, AuthorizationManager $authorization)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
            $error = false;
            $permissions = $this->permissionRepo->findAll();
            if(count($permissions) < 1) {
                $error = true;
            }

            $categories = $CategoryPermissionService->getAllCategoryPermission();
            if (count($categories) > 0) {
                $tabCategories = [];
                foreach ($categories as $key => $categorie) {
                    $permissionsCategorie = $PermissionService->getAllPermissionByCategorie($categorie);

                    $tabCategories[$categorie->getTitle()] = [];

                    if ($permissionsCategorie != 0) {
                        foreach ($permissionsCategorie as $keyPermission => $permissionCategorie) {
                            $infoPermission = [];
                            $state = $authorization::isPrivilegeAcceptedByPermission($privilege, $permissionCategorie->getTitle());
                            if (!in_array($permissionCategorie->getTitle(), $tabCategories[$categorie->getTitle()])) {
                                $infoPermission['id'] = $permissionCategorie->getId();
                                $infoPermission['title'] = $permissionCategorie->getTitle();
                                $infoPermission['state'] = $state;
                                array_push($tabCategories[$categorie->getTitle()], $infoPermission);
                            }
                        }
                    }
                }

                $data['exception'] = "";
                $data["html"] = $this->renderView('admin/privileges/assignation.html.twig', [
                    'tabCategories' => $tabCategories,
                    'id' => $privilege->getId(),
                    'title' => $privilege->getTitle(),
                    'error' => $error

                ]);
                return new JsonResponse($data);
            } 

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/privileges/assignation.html.twig', [
                'id' => $privilege->getId(),
                'title' => $privilege->getTitle(),
                'error' => $error
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
            return new JsonResponse($data);
            $this->createNotFoundException('Exception' . $Exception->getMessage());
        }
    }

    #[Route('/assignation/permission/validation/{privilege}', name: '_assignation_permission_validation')]
    public function assignationValidation(Request $request, CategoryPermissionService $CategoryPermissionService, PermissionService $PermissionService, Privilege $privilege, AuthorizationManager $authorization)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        $permissionsPrivilege = $request->get('permission_privilege');
      
        if (null != $permissionsPrivilege && is_array($permissionsPrivilege) && sizeof($permissionsPrivilege) > 0) {
            $permissionsInitial = $privilege->getPermissions();

            if (sizeof($permissionsInitial) > 0) {
                foreach ($permissionsInitial as $key => $permissionInitial) {
                    $privilege->removePermission($permissionInitial);
                    $permissionInitial->removePrivilege($privilege);
                }
            }

            foreach ($permissionsPrivilege as $key => $idPermission) {
                $permission = $PermissionService->getPermissionById($idPermission);
                $this->privilegeService->addPermission($privilege, $permission);
                $PermissionService->addPrivilege($permission, $privilege);
            }
            if ($request->isXmlHttpRequest()) {
                $this->privilegeService->update();
                return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
            }

            //$this->addFlash('success', 'Assignation permission au "' . $privilege->getTitle() . '" avec succès.');
            //return $this->redirectToRoute('privilege_assignation_permission', ['privilege' => $privilege->getId()]);
        } else {
            // remove all permissions du privilege
            if ($request->isXmlHttpRequest()) {
                if(count($privilege->getPermissions()) > 0) {
                    $this->privilegeService->removeAllPermissionPrivilege($privilege);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                } 
               
            }
            //$this->addFlash('success', 'Assignation permission au "' . $privilege->getTitle() . '" avec succès.');
            //return $this->redirectToRoute('privilege_assignation_permission', ['privilege' => $privilege->getId()]);
        }
    }
}