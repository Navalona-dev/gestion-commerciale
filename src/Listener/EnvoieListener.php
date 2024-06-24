<?php
namespace App\Listener;
use App\Entity\Envoie;
//use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\NiveauhierarchiqueRepository;
use App\Repository\DossierRepository;
use App\Service\LogService;

class EnvoieListener
{
    public function __construct(TokenStorageInterface  $TokenStorageInterface, NiveauhierarchiqueRepository $niveauhierarchiqueRepository, DossierRepository $dossierRepository, LogService $logService)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->niveauhierarchiqueRepository = $niveauhierarchiqueRepository;
        $this->dossierRepository = $dossierRepository;
        $this->logService = $logService;
    }

    public function postPersist(LifecycleEventArgs $event) {
        
        $entity= $event->getEntity();
        
        if ($entity instanceof Envoie) {
            $infoDossier = $this->dossierRepository->getInfoDossierById($entity->getDossier());
            
            if ($infoDossier != false) {
               $niveauHierarchiqueUserSource = $this->tokenStorage->getToken()->getUser()->getNiveauHierarchique();
               $niveauHierarchiqueUserDestinataire = $entity->getUserDestinataireHierarchique();
               $infoNiveauSourceInfo = $this->niveauhierarchiqueRepository->getInfoNiveauHierarchique($entity->getService(), $infoDossier["idregion"],$infoDossier["idcommune"], $niveauHierarchiqueUserSource);
               $serviceDestinataire = "";

               switch ($entity->getVers()) {
                case 'Commune':
                    $serviceDestinataire = "Communale";
                    break;
                case 'SRAT':
                    $serviceDestinataire = "SRAT";
                    break;
                case 'Apipa':
                    $serviceDestinataire = "Apipa";
                    break;
                case 'Ministere':
                    $serviceDestinataire = "Ministeriel";
                    break;
                case 'Client':
                    $serviceDestinataire = "Client";
                    break;
                default:
                    $serviceDestinataire = $entity->getService();
                    break;
               }

               $infoNiveauDestinataireInfo = $this->niveauhierarchiqueRepository->getInfoNiveauHierarchique($serviceDestinataire, $infoDossier["idregion"],$infoDossier["idcommune"], $niveauHierarchiqueUserDestinataire);
               $this->logService->addLog($infoDossier, $infoNiveauSourceInfo, $infoNiveauDestinataireInfo, "ENVOIE", $entity);
               
            }
            return;
        }
    }

    /*public function __invoke(Privilege $privilege, LifecycleEventArgs $event)
    {
        $entityManager = $event->getEntityManager(); prePersist
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
    }*/
}