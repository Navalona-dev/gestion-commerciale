<?php
namespace App\Service;

use App\Service\AuthorizationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Exception\PropertyVideException;
use App\Exception\ActionInvalideException;
use App\Entity\Categoryofpermission;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class CategoryPermissionService
{
    private $tokenStorage;
    private $authorization;
    public  $isCurrentDossier = false;
    private $entityManager;

    public function __construct(AuthorizationManager $authorization, TokenStorageInterface  $TokenStorageInterface, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
    }

    public function add($instance)
    {
        $categoryofPermission = Categoryofpermission::newCategoryofpermission($instance->getTitle());
        $categoryofPermission->setDescription($instance->getDescription());
        $this->entityManager->persist($categoryofPermission);
        $this->update();
        unset($instance);
        return $categoryofPermission;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($categoryofPermission)
    {
        $permissions = $categoryofPermission->getPermissions();
        foreach($permissions as $permission) {
            $this->entityManager->remove($permission);
        }
        $this->entityManager->remove($categoryofPermission);
        $this->update();
    }

    public function getAllCategoryPermission()
    {
        $categoryofPermissions = $this->entityManager->getRepository(Categoryofpermission::class)->findAll();
        if (count($categoryofPermissions) > 0) {
            return $categoryofPermissions;
        }
        return false;
    }

}