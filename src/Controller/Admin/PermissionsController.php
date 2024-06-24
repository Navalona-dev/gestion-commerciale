<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\ORMInvalidArgumentException;
use App\Exception\PropertyVideException;
use Doctrine\Persistence\Mapping\MappingException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Exception\UnsufficientPrivilegeException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use App\Entity\Permission;
use App\Service\PermissionService;
use App\Form\PermissionType;
use App\Service\AccesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin/permissions', name: 'permissions')]
class PermissionsController extends AbstractController
{
    private $accesService;
    public function __construct(AccesService $AccesService)
    {
        $this->accesService = $AccesService;
    }

    #[Route('/', name: '_liste')]
    public function list(PermissionService $PermissionService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {

            $permissions = $PermissionService->getAllPermissions();

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
    public function create(Request $request, PermissionService $PermissionService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $permission = new Permission();

        $form = $this->createForm(PermissionType::class, $permission);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    $PermissionService->add($permission);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }

                $this->addFlash('success', 'Ajout permission "' . $permission->getTitle() . ' " avec succès.');
                return $this->redirectToRoute('permissions_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/permissions/new.html.twig', [
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
    public function edit(Request $request, PermissionService $PermissionService, Permission $permission)
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
                    $PermissionService->update();
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
    public function delete(Request $request, PermissionService $permissionService, Permission $permission)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $permissionService->remove($permission);
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