<?php
namespace App\Service;

use App\Service\AuthorizationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Exception\PropertyVideException;
use App\Exception\ActionInvalideException;
use App\Entity\Privilege;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class PrivilegeService
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
        $privilege = Privilege::newPrivilege($instance->getTitle());
        $privilege->setDescription($instance->getDescription());
        $this->entityManager->persist($privilege);
        $this->update();
        unset($instance);
        return $privilege;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($privilege)
    {
        $this->entityManager->remove($privilege);
        $this->update();
    }

    public function getAllPrivilege()
    {
        $privileges = $this->entityManager->getRepository(Privilege::class)->findAll();
        if (count($privileges) > 0) {
            return $privileges;
        }
        return false;
    }

    public function getAllPermission($privilege) {
        $permissions = $privilege->getPermissions();
        if (count($permissions) > 0) {
            return $permissions;
        }
        return false;
    }

    public function addPermission($privilege, $permission)
    {
        $privilege->addPermission($permission);
    }

    public function removePermissionPrivilege($privilege, $permission)
    {
        $privilege->removePermission($permission);
    }

    public function removeAllPermissionPrivilege($privilege) {
        $permissions = $this->getAllPermission($privilege);
        if (count($permissions) > 0) {
            foreach ($permissions as $key => $permission) {
                    $this->removePermissionPrivilege($privilege, $permission);
                    $permission->removePrivilege($privilege);
            }
            $this->update();
        }
    }

}