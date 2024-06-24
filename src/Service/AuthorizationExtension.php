<?php
namespace App\Service;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Entity\User;
use App\Service\AuthorizationManager;
use App\Service\TraitementService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorizationExtension extends AbstractExtension
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface  $TokenStorageInterface )
    {
        $this->tokenStorage = $TokenStorageInterface;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('isUserAllowedOnFeature', [$this, 'isUserAllowedOnFeature']),
            new TwigFunction('isUserGrantedPrivilege', [$this, 'isUserGrantedPrivilege']),
        ];
    }

    public function isUserAllowedOnFeature(string $permission) {
        if (null === $token = $this->tokenStorage->getToken()) {
            return array();
        }

        $user = $token->getUser();

       return  AuthorizationManager::isUserAllowedOnFeature($user, $permission);
    }

    public function isUserGrantedPrivilege(string $privilege) {
        if (null === $token = $this->tokenStorage->getToken()) {
            return array();
        }

        $user = $token->getUser();

       return  AuthorizationManager::isUserGrantedPrivilege($user, $privilege);
    }
}