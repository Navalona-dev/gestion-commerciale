<?php

namespace App\Service;

use App\Entity\Permission;
use App\Entity\Privilege;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class AuthorizationManager
{

    /** Obtenir tous les permissions de l'utilisateur */
    static public function getAllowedPermissions (User $user) {
        $privileges = self::getUserGrantedPrivileges($user);
        $arrayPermissions = [];
        foreach($privileges as $privilege) {
            $permissions = self::getPermissionAssociedOfPrivileges($privilege);
            foreach($permissions as $permission) {
                if(!in_array($permission, $arrayPermissions)) {
                        array_push($arrayPermissions, $permission);
                }
            }
        }
        return $arrayPermissions;
    }
    /** obtenir tous les privileges de l'user current */
    static public function getUserGrantedPrivileges (User $user) {
        return $user->getPrivileges();
    }
    /** Retourner la liste des utilisateurs ayant reçu le privilege donnée */
    static public function getGrantedUsers(Privilege $privilege) {
        return $privilege->getUsers();
    }
    /** obtenir tous les permissions associé à un privilege */
    static public function getPermissionAssociedOfPrivileges (Privilege $privilege) {
        return $privilege->getPermissions();
    }
    /** Retourner la liste des privilèges acceptés par la fonctionnalité $permission. */
    static public function getAcceptedPrivileges(Permission $permission) {
            return $permission->getPrivileges();
    }
    /** Lister les utilisateurs ayant accès à la permission $permission */
    static public function getAllowedUsers(Permission $permission) {
            $privileges = self::getAcceptedPrivileges($permission);
            $tabUsers = [];
            foreach($privileges as $privilege) {
                    $users = self::getGrantedUsers($privilege);
                    if(!in_array($users, $tabUsers)) {
                        array_push($tabUsers, $users);
                    }
            }
            return $tabUsers;
    }
    /** check if user allowed in feature */
    static public function isUserAllowedOnFeature(User $user, string $permissionTocheck) {

        $isAllowed = false;

        $privileges = self::getUserGrantedPrivileges($user);

        $arrayAllowedPermissions = [];

        foreach($privileges as $privilege) {
            $permissions = self::getPermissionAssociedOfPrivileges($privilege);
            foreach($permissions as $permission) {
                if($permission->getTitle() == $permissionTocheck) {
                    $isAllowed = true;
                    break 2;
                }
            }
        }
        return $isAllowed;
    }
    static public function isUserGrantedPrivilege(User $user, string $privilegeTocheck) {
        $isGranted = false;

        $privileges = self::getUserGrantedPrivileges($user);

        foreach($privileges as $privilege) {
                if($privilege->getTitle() == $privilegeTocheck) {
                    $isGranted = true;
                    break;
                }
        }
        return $isGranted;
    }
    /** Est-ce que le privilège  est accepté par la fonctionnalité $permission. */
    static public function isPrivilegeAcceptedByPermission(Privilege $privilege, string $permissionTocheck) {
        $permissions = self::getPermissionAssociedOfPrivileges($privilege);
        $isFound = false;
        foreach($permissions as $permission) {
            if($permission->getTitle() == $permissionTocheck) {
                $isFound = true;
                break;
            }
        }
        return $isFound;
    }
    /**
     * Est-ce que le privilège $privilege est attribué à l'utilisateur $user ?
     */
    static public function isPrivilegeGrantedToUser(Privilege $privilege, User $user) {
                $privilegesUser = self::getUserGrantedPrivileges($user);
                $isFound = false;
                foreach($privilegesUser as $privUser) {
                    if($privUser->getId()  == $privilege->getId()) {
                        $isFound = true;
                        break;
                    }
                }
                return $isFound;
    }
    /** Est-ce que la permission $permission accepte le privilège $privilege ? */
    static public function isPermissionAcceptingPrivilege(Permission $permission, string $privilegeTocheck) {

        $privileges = self::getAcceptedPrivileges($permission);

        $IsFound = false;
        foreach($privileges as $privilege) {
            if($privilege->getTitle() == $privilegeTocheck) {
                $IsFound = true;
                break;
            }
        }
        return $IsFound;
    }
    /** Est-ce que la permission  $permission autorise l'utilisateur $user ? */
    static public function isPermissionAllowingUser(Permission $permission, User $user) {

            $privilegesPermission = self::getAcceptedPrivileges($permission);
            $privilegesUser = self::getUserGrantedPrivileges($user);

            $isAllowed = false;
            foreach ($privilegesPermission as $privPermission) {
                foreach ($privilegesUser as $privUser) {
                    if ($privPermission->getId() == $privUser->getId()) {
                        $isAllowed = true;
                        break;
                    }
                }
            }
            return $isAllowed;
    }
}
