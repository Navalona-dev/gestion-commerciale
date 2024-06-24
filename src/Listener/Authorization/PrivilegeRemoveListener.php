<?php
namespace App\Listener\Authorization;
use App\Entity\Privilege;
use App\Entity\Permission;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class PrivilegeRemoveListener
{
    public function __invoke(Privilege $privilege, LifecycleEventArgs $event)
    {
        $entityManager = $event->getEntityManager();
        $users = $privilege->getUsers();
        $permissions = $privilege->getPermissions();
        // 2. Supprimer $privilege associé à l'utilisateur.
        // Enlever l'user associé au privilege.
        if ($users) {
            foreach ($users as $user) {
                $privilege->removeUser($user);
                $user->removePrivilege($privilege);
            }
        }

        // 3. Enlever au privilege le $permission.
        // Enlever la permission associé au privilege
        if ($permissions) {
            foreach ($permissions as $permission) {
                $privilege->removePermission($permission);
                $permission->removePrivilege($privilege);
            }
        }

        // 3. We upadate our database
        $entityManager->flush();
    }
}