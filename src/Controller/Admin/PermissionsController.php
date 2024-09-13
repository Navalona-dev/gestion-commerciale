<?php

namespace App\Controller\Admin;

use App\Entity\Permission;
use App\Form\PermissionType;
use App\Service\AccesService;
use App\Service\PermissionService;
use App\Service\ApplicationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\UnsufficientPrivilegeException;
use App\Repository\CategoryofpermissionRepository;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpClient\Exception\ServerException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/permissions', name: 'permissions')]
class PermissionsController extends AbstractController
{
    private $permissionService;
    private $accesService;
    private $application;
    private $categoriePermissionRepo;

    public function __construct(
        PermissionService $permissionService, 
        ApplicationManager $applicationManager, 
        AccesService $accesService,
        CategoryofpermissionRepository $categoriePermissionRepo
        )
    {
        $this->permissionService = $permissionService;
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
        $this->categoriePermissionRepo = $categoriePermissionRepo;
    }

    #[Route('/', name: '_liste')]
    public function list()
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {

            $permissions = $this->permissionService->getAllPermissions();

            if ($permissions == false) {
                $permissions = [];
            }
            $data["html"] = $this->renderView('admin/permissions/index.html.twig', [
                'listes' => $permissions,
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
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $permission = new Permission();

        $form = $this->createForm(PermissionType::class, $permission);
        $data = [];
        try {

            $categoriePermissions = $this->categoriePermissionRepo->findAll();

            $error = false;

            if(count($categoriePermissions) < 1) {
                $error = true;
                return new JsonResponse(['status' => 'error']);
            }

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    $this->permissionService->add($permission);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }

                $this->addFlash('success', 'Ajout permission "' . $permission->getTitle() . ' " avec succès.');
                return $this->redirectToRoute('permissions_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/permissions/new.html.twig', [
                'form' => $form->createView(),
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

        return new JsonResponse($data);
    }

    #[Route('/{permission}', name: '_edit')]
    public function edit(Request $request, Permission $permission)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(PermissionType::class, $permission, ['isEdit' => true]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $this->permissionService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                $this->addFlash('success', 'Modification permission "' . $permission->getTitle() . '" avec succès.');
                return $this->redirectToRoute('permissions_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/permissions/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $permission->getId(),
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

    #[Route('/delete/{permission}', name: '_delete')]
    public function delete(Request $request, Permission $permission)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->permissionService->remove($permission);
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
}