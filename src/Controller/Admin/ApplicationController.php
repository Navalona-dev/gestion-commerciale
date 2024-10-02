<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Form\ApplicationType;
use App\Service\AccesService;
use App\Service\ApplicationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\ApplicationService;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Exception\UnsufficientPrivilegeException;
use Doctrine\Persistence\Mapping\MappingException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/applications', name: 'applications')]
class ApplicationController extends AbstractController
{
    private $applicationService;
    private $accesService;
    private $application;
    
    public function __construct(ApplicationService $applicationService, ApplicationManager $applicationManager, AccesService $accesService)
    {
        $this->applicationService = $applicationService;
        $this->accesService = $accesService;
        $this->application = $applicationManager->getApplicationActive();
    }

    #[Route('/change-appli/{id}', name: '_change_appli')]
    public function changeAppli(
        Application $application,
        Request $request)
    {
        $user = $this->getUser();

        $uri = $request->get('uri');

        $user->setAppActive($application);
        $this->applicationService->persist($user);
        $this->applicationService->update();

        if (preg_match("/compte|financiere|tache|affaire|agenda|home/", $uri)) {
            return $this->redirect("/gomyclic/home");
        }

        $this->addFlash('success', 'Vous êtes basculé sur l\' application : '.$application->getEntreprise().' ');

        return $this->redirect($uri);
    }

    #[Route('/', name: '_liste')]
    public function list()
    {
        /*if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('index_front'); // To DO page d'alerte insufisance privilege
        }*/
        $data = [];
        try {

            $applications = $this->applicationService->getAllApplications();

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
    public function create(Request $request)
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
                    
                    $this->applicationService->add($application);
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
    public function edit(Request $request, Application $application)
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
                    try {
                        $this->applicationService->persist($application);
                        $this->applicationService->update();
                        //dd($application->getLogoFile(), $application->getLogoName());
                    } catch (\Exception $e) {
                        // Log l'erreur ou affichez-la
                            dd($e->getMessage());
                    }
                   
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
    public function delete(Request $request, Application $application)
    {
       /* if (!$this->accesService->insufficientPrivilege('oatf')) {
            return $this->redirectToRoute('app_logout'); // To DO page d'alerte insufisance privilege
        }*/
        try {
           
            if ($request->isXmlHttpRequest()) {
                $this->applicationService->remove($application);
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

    #[Route("/update-is-active/{id}", name:"app_is_active_application")]
    public function updateIsActive(Request $request, EntityManagerInterface $em, Application $application): JsonResponse
    {
        if (!$application) {
            throw $this->createNotFoundException('Aucune entité trouvée pour l\'identifiant. '.$id);
        }

        $application->setIsActive(!$application->getIsActive());
        $em->persist($application);
        $em->flush();

        // Renvoyer une réponse JSON avec l'état mis à jour
        return new JsonResponse(['isActive' => $application->getIsActive()]);
    }
}