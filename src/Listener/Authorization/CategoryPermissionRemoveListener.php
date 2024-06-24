<?php
namespace App\Listener\Authorization;
use App\Entity\Categoryofpermission;
use App\Entity\Permission;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Exception\EntityNotFoundException;

class CategoryPermissionRemoveListener
{
    public function __invoke(Categoryofpermission $Categoryofpermission, LifecycleEventArgs $event)
    {

        //$entity = $event->getEntity();

        $entityManager = $event->getEntityManager();
        $permissions = $entityManager->getRepository(Permission::class)->findBy(['categoryofpermission' => $Categoryofpermission]);

        // 2. Supprimer tous les permissions rattaché a ce category.
        if ($permissions) {

            foreach ($permissions as $permission) {
                $entityManager->remove($permission);
            }

            // 3. Update our database
            $entityManager->flush();
        }
    }
}