<?php

namespace App\Service;

use App\Entity\AvisAdministratif;
use App\Service\AuthorizationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\User;
use App\Exception\PropertyVideException;
use App\Exception\ActionInvalideException;
use Doctrine\Common\Persistence\ManagerRegistry;
// pdf
use Dompdf\Dompdf;
use Dompdf\Options;
// QrCode
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

use Doctrine\ORM\EntityManager;

class AccesService
{
    private $tokenStorage;
    private $authorization;
    private $managerRegistry;
    private $session;
    public  $isCurrentDossier = false;
    private $currentUser = null;
    public function __construct(AuthorizationManager $authorization, TokenStorageInterface  $TokenStorageInterface)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;

        $this->currentUser = $this->tokenStorage->getToken()->getUser();
    }

    public function insufficientPrivilege($privilege)
    {
        if ($this->currentUser instanceof User) {
            return $this->currentUser->isUserGrantedPrivilege($privilege);
        }
        return false;
        //throw new ActionInvalideException("Veuillez connecter");
    }

    public function isUserAllowedOnFeature($permission)
    {
        if ($this->currentUser instanceof User) {
            return $this->currentUser->isUserAllowedOnFeature($permission);
        }
        return false;
        //throw new ActionInvalideException("Veuillez connecter");
    }
}