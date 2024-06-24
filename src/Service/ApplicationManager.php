<?php

/*
 * Sur chacune de vos routes, en dehors de app_login, application_index, app_logout,
 * vous devez faire en sorte que Symfony\Component\HttpFoundation\Request soit bien injecté
 * et mettre les lignes suivantes en début de code :
 *
 * if (!$request->getSession()->get('application', false)) {
 *    $this->addFlash('danger', 'Vous n\'avez pas prédéfinie votre application !');
 *    return $this->redirectToRoute('application_index');
 * }
 */

namespace App\Service;

use App\Entity\Application;
use App\Repository\ApplicationRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;

class ApplicationManager
{
    static $tokenStorage;
    static $applicationRepository;
    static $applicationSelected;

    public function __construct(TokenStorageInterface $tokenStorage, ApplicationRepository $applicationRepository)
    {
        self::$tokenStorage = $tokenStorage;
        self::$applicationRepository = $applicationRepository;
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        $this->appDefault = $applicationRepository->findOneBy(['id' => 1]);
        self::$applicationSelected = $applicationRepository->findOneBy(['id' => 1]);

        $this->applicationAll = $applicationRepository->findAll();
    }

    public function getApplicationActive()
    {

        if ($this->user && $this->user != "anon.") {
            return $this->user->getAppActive();
        }else{
            return $this->appDefault;
        }

    }

    static public function getCurrentApplication () {
        $user = self::$tokenStorage->getToken() ? self::$tokenStorage->getToken()->getUser() : null;
        $appDefault = self::$applicationRepository->findOneBy(['id' => 1]);
        if ($user && $user != "anon.") {
            return $user;
        }else{
            return $appDefault;
        }
    }
    
    static public function setApplicationSelected($application = null)
    {
        if (null != $application) {
            self::$applicationSelected = $application;
        }
    }
    
    static public function getApplicationSelected()
    {
            return self::$applicationSelected;
    }
}
