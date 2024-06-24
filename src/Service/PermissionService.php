<?php
namespace App\Service;

use App\Service\AuthorizationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Exception\PropertyVideException;
use App\Exception\ActionInvalideException;
use App\Entity\Permission;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class PermissionService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $session;
    public  $isCurrentDossier = false;

    public function __construct(AuthorizationManager $authorization, TokenStorageInterface  $TokenStorageInterface, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
    }

    public function add($instance)
    {
        $permission = Permission::newPermission($instance->getCategoryofpermission(), $instance->getTitle());

        $permission->setDescription($instance->getDescription());

        $this->entityManager->persist($permission);
        $this->update();
        unset($instance);
        return $permission;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($permission)
    {
        $this->entityManager->remove($permission);
        $this->update();
    }

    public function getAllPermissions()
    {
        $permissions = $this->entityManager->getRepository(Permission::class)->findAll();
        if (count($permissions) > 0) {
            return $permissions;
        }
        return false;
    }

    public function getAllPermissionByCategorie($categorie)
    {
        $permissions = $this->entityManager->getRepository(Permission::class)->findBy(['categoryofpermission' => $categorie]);
        if (count($permissions) > 0) {
            return $permissions;
        }
        return false;
    }

    public function getPermissionById($id)
    {
        $permission = $this->entityManager->getRepository(Permission::class)->find($id);
        if ($permission) {
            return $permission;
        }
        return null;
    }

    public function addPrivilege($permission, $privilege)
    {
        $permission->addPrivilege($privilege);
    }

    public function removePrivilege($privilege, $permission)
    {
        $permission->removePrivilege($privilege);
    }

}