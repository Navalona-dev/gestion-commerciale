<?php

// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use App\Entity\Site;
use App\Entity\Gomyclic\Droit\Role;
use App\Service\ApplicationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use App\Repository\Gomyclic\Droit\PageRepository;
use App\Repository\Gomyclic\Droit\RoleRepository;
use App\Repository\Gomyclic\ApplicationRepository;
use App\Controller\Gomyclic\EnvironnementController;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Repository\Gomyclic\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
class RequestListener implements EventSubscriberInterface
{
    private $em;
    private $session;
    private $tokenStorage;
    private $applicationManager;
    private $security;

    public function __construct(EntityManagerInterface $em, 
                                TokenStorageInterface $tokenStorage,
                                RouterInterface $router,
                                EventDispatcherInterface $dispatcher,
                                //Security $security,
                                //SessionInterface $session
                                )
    {
        $this->entityManager = $em;
        //$this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        //$this->entityManager=$entityManager;
        $this->dispatcher = $dispatcher;
        //$this->security = $dispatcher;
    }
    
    public function onKernelRequest(RequestEvent $event)
    {
        
        $host = $event->getRequest()->getHost();
        $this->path= $event->getRequest()->getRequestUri();
        $uri = $event->getRequest()->getUri();
        //escape url mise à jour element
        
        $token=$this->tokenStorage->getToken();
       
        if($token){
            $this->user = $token->getUser();
           
            
            
                if(!is_string($this->user)){
                $this->application=$this->user->getAppActive();

                
                }
        
        }
      
        if (null === $this->tokenStorage->getToken()) { //$this->security->isGranted('IS_AUTHENTICATED_FULLY')
        } elseif (!empty($this->tokenStorage->getToken())) { // In debug mode, it's null
            $user = $this->tokenStorage->getToken()->getUser();

            //$this->session->set('appActive', $user->getAppActive());

            /*if (method_exists($user, "getAppActive") && null == $user->getAppActive()) {
                $application = $user->getApplication();

                if (sizeof($application)>0) {
                    $user->setAppActive($application[0]);
                    $this->em->persist($user);
                    $this->em->flush();
                }else {




                }
            }*/

            if ($user && null != $user && method_exists($user, "getEmail")) {
                $uri = $event->getRequest()->getUri();

                if (preg_match("/(_fragment|breves_rss|favicon.ico|flash-bag|from-ajax-type-head|columns%)/i", $uri)) {

                } else {
                    $request = $event->getRequest();

                    $ipUser = $event->getRequest()->getClientIp();

                    $emailUser = $user->getEmail();

                    $dossierUser = "./uploads/historique/" . $emailUser;

                   /* if (!is_dir($dossierUser)) {

                        @mkdir($dossierUser, 0777);
                        
                    }*/
                    // Création du répertoire historique de l'utilisateur
                    if (!is_dir($dossierUser)) {
                        if (!mkdir($dossierUser, 0777, true)) {
                            error_log("Échec de la création du répertoire : " . $dossierUser);
                        }
                    }

                    $application = "";


                    if (method_exists($user, 'getAppActive')) {
                        $appActive = $user->getAppActive();
                        if ($appActive !== null) {
                            $application = $appActive->getEntreprise();
                        } else {
                            $application = 'Point de vente'; 
                        }
                    }


                    $myfile = @fopen($dossierUser . "/historique_" . date("Y-m-d") . ".txt", "a+");
                   
                    @fwrite($myfile, date("Y-m-d H:i:s") . ' - Application : '.$application.' - url : ' . $uri . " - " . $ipUser . "\n");
                    @fclose($myfile);


                    $applicationActive = $user->getAppActive();
                    //check directory sur l'application
                    //créer les repertoires pour les documents de l'application
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()))
                        @mkdir('./uploads/APP_'.$applicationActive->getId(), 0777);
                    //document
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/documents"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/documents", 0777);
                    //Files
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/documents/files"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/documents/files", 0777, true);
                    //Factures fournisseur
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/documents/factures"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/documents/factures", 0777, true);

                    //logos
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/logos"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/logos", 0777);

                    //document et image candidat
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/documents/cv"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/documents/cv", 0777, true);
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/documents/candidature"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/documents/candidature", 0777, true);

                    //document utilsateur
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/documents/utilisateur"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/documents/utilisateur", 0777, true);
                    //Fiche de frais
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/documents/fichedefrais"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/documents/fichedefrais", 0777, true);
                    //Image ressource
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/ressources"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/ressources", 0777);
                    //login
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/login"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/login", 0777);
                    //Image produits
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/produits"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/produits", 0777);

                    //Bon de livraison signé
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/signed"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/signed", 0777);
                    
                    //Factures fournisseur
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/factures"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/factures", 0777);

                    //Reporting
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/reporting"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/reporting", 0777);

                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/imports"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/imports", 0777);

                    if ($applicationActive && !is_dir('./uploads/factures/valide/')) {
                        @mkdir('./uploads/factures/valide/', 0777, true);                        
                    }
                    

                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/avatar")) {
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/avatar", 0777);                     
                    }

                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/historique")) {
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/historique", 0777);                     
                    }

                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/product")) {
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/product", 0777);                     
                    }

                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/factures/annule"))
                        @mkdir('./uploads/APP_'.$applicationActive->getId()."/factures/annule", 0777);
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/factures/valide"))
                    @mkdir('./uploads/APP_'.$applicationActive->getId()."/factures/valide", 0777);
                    if ($applicationActive && !file_exists('./uploads/APP_'.$applicationActive->getId()."/factures/echeance"))
                    @mkdir('./uploads/APP_'.$applicationActive->getId()."/factures/echeance", 0777);

                    
                }
            }

        }

       // return $this->session->set('host', $host);
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
