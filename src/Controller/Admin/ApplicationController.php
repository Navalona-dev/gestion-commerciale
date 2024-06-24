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
use App\Entity\Application;
use App\Service\ApplicationService;
use App\Form\ApplicationType;
use App\Service\AccesService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Route('/admin/applications', name: 'applications')]
class ApplicationController extends AbstractController
{
    private $accesService;
    public function __construct(AccesService $AccesService)
    {
        $this->accesService = $AccesService;
    }

    #[Route('/', name: '_liste')]
    public function list(ApplicationService $applicationService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {

            $applications = $applicationService->getAllApplications();

            if ($applications == false) {
                $applications = [];
            }
            $data["html"] = $this->renderView('admin/applications/index.html.twig', [
                'listes' => $applications,
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
    public function create(Request $request, ApplicationService $applicationService)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $application = new Application();

        $form = $this->createForm(ApplicationType::class, $application);
        $data = [];
        try {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                if ($request->isXmlHttpRequest()) {
                    
                    $applicationService->add($application);
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }

                //$this->addFlash('success', 'Ajout application "' . $application->getTitle() . ' " avec succès.');
                //return $this->redirectToRoute('applications_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/applications/new.html.twig', [
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

    #[Route('/{application}', name: '_edit')]
    public function edit(Request $request, ApplicationService $applicationService, Application $application)
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {
            $form = $this->createForm(ApplicationType::class, $application, []);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($request->isXmlHttpRequest()) {
                    $applicationService->update();
                    return new JsonResponse(['status' => 'success'], Response::HTTP_OK);
                }
                //$this->addFlash('success', 'Modification application "' . $application->getTitle() . '" avec succès.');
                //return $this->redirectToRoute('applications_liste');
            }

            $data['exception'] = "";
            $data["html"] = $this->renderView('admin/applications/modal_update.html.twig', [
                'form' => $form->createView(),
                'id' => $application->getId(),
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

    #[Route('/delete/{application}', name: '_delete')]
    public function delete(Request $request, ApplicationService $applicationService, Application $application)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $applicationService->remove($application);
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