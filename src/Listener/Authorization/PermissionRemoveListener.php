<?php
namespace App\Listener\Authorization;
use App\Entity\Privilege;
use App\Entity\Permission;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class PermissionRemoveListener
{
    public function __invoke(Permission $permission, LifecycleEventArgs $event)
    {
        $entityManager = $event->getEntityManager();
        $privileges = $permission->getPrivileges();

        // 2. Supprimer $permission dans les privileges qui lui sont attribué.
        // Enlever au permission les privileges de $permission
        if ($privileges) {

                foreach ($privileges as $privilege) {
                    $permission->removePrivilege($privilege);
                    $privilege->removePermission($permission);
                }

                // 3. We upadate our database
                $entityManager->flush();
        }
    }
}