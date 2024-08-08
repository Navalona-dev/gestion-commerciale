<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Categorie;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Entity\DatePeremption;
use Doctrine\ORM\EntityManager;
use App\Service\ApplicationManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StockService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $session;
    public  $isCurrentDossier = false;
    private $logger;
    private $security;

    public function __construct(
        ApplicationManager  $applicationManager, 
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        LoggerInterface $productLogger, 
        Security $security)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
        $this->logger = $productLogger;
        $this->security = $security;

    }

    public function add($instance, $produitCategorie, $datePeremption)
    {
        $date = new \DateTime();

        $formattedDatePeremption = $datePeremption->format('d-m-Y');

        $stocks = $this->entityManager->getRepository(Stock::class)->findBy(['produitCategorie' => $produitCategorie]);
        $existingStock = null;

        // Trouver un stock existant avec la même date de péremption
        foreach ($stocks as $stock) {
            if($stock->getDatePeremption()) {
                $formattedDatePeremptionStock = $stock->getDatePeremption()->getDate()->format('d-m-Y');
                if ($formattedDatePeremptionStock === $formattedDatePeremption) {
                    $existingStock = $stock;
                    break; 
                }
            }
        }

        if ($existingStock) {
            // Mettre à jour le stock existant
            $oldQtt = $existingStock->getQtt();
            $oldQttRestant = $existingStock->getQttRestant();
            $newQtt = $instance->getQtt();
            $existingStock->setQtt($oldQtt + $newQtt);
            $existingStock->setQttRestant($oldQttRestant + $newQtt);
            $newStock = $existingStock; 
            $this->entityManager->persist($newStock);

        } else {
            // Créer un nouveau stock
            $newStock = Stock::newStock($instance);
            $newStock->setDateCreation($date);
            $newStock->setQttRestant($instance->getQtt());
            $newStock->setProduitCategorie($produitCategorie);
              // Initialisation de la date de péremption
            if ($datePeremption === null || $datePeremption === '') {
                $newStock->setDatePeremption(null);
            } else {
                $newDatePeremption = new DatePeremption();
                $newDatePeremption->setDate($datePeremption);
                $newDatePeremption->setDateCreation($date);

                $this->entityManager->persist($newDatePeremption);
                $newStock->setDatePeremption($newDatePeremption);
            }
            $this->entityManager->persist($newStock);
        }


        $stockProduit = ($produitCategorie->getStockRestant() === null) ? 0 : $produitCategorie->getStockRestant();

        $stockRestant = $stockProduit + $newStock->getQtt();
        $stockMax = $produitCategorie->getStockMax();

        $produitCategorie->setStockRestant($stockRestant);

        $this->entityManager->persist($produitCategorie);

        if($stockRestant >= $stockMax) {
            $notification = new Notification();
                $message = 'Le stock du produit ' . '<strong>' . $produitCategorie->getNom() . '</strong>' . ' est surchargé, vous ne devez plus ajouter jusqu\'à nouvelle ordre';
                $notification->setMessage($message)
                             ->setDateCreation(new \DateTime())
                             ->setApplication($this->application)
                             ->setProduitCategorie($produitCategorie)
                             ->setStockMax(true);
                $this->entityManager->persist($notification);
        }

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

         // Créer log
         $this->logger->info('Stock de produit catégorie ajouté', [
             'Produit' => $produitCategorie->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
             'ID Application' => $produitCategorie->getApplication()->getId()
         ]);

        $this->update();
        unset($instance);
        return $newStock;
    }

    public function edit($stock, $produitCategorie, $oldQtt, $datePeremption)
    {
        // Obtenez la quantité et le stock restant actuels
        $oldStockRestant = $produitCategorie->getStockRestant();
        // Initialisation de $newDatePeremption
        $newDatePeremption = null;
    
        // Obtenez l'ID de la date de péremption actuelle du stock
        $datePeremptionId = $stock->getDatePeremption() ? $stock->getDatePeremption()->getId() : null;
    
        // Vérifiez si une nouvelle date de péremption est fournie
        if ($datePeremption != null) {
            if ($datePeremptionId == null) {
                // Créez une nouvelle DatePeremption si elle n'existe pas déjà
                $newDatePeremption = new DatePeremption();
                $newDatePeremption->setDate($datePeremption);
                $newDatePeremption->setDateCreation(new \DateTime());
                $this->entityManager->persist($newDatePeremption);
            } else {
                // Si la date de péremption existe déjà, récupérez-la
                $newDatePeremption = $this->entityManager->getRepository(DatePeremption::class)->find($datePeremptionId);
                $newDatePeremption->setDate($datePeremption);
            }
        }

        $oldQttRestant = $stock->getQttRestant();

        //si oldQtt est egale à oldQttRestant
        if ($oldQtt == $oldQttRestant) {
            // Cas où la quantité ancienne est égale à la quantité restante
            $newQtt = $stock->getQtt();
            $stock->setQttRestant($newQtt);
        } elseif ($oldQtt > $oldQttRestant) {
            $newQtt = $stock->getQtt();

            if($newQtt > $oldQtt) {
                $newQttRestant = $oldQttRestant + ($newQtt - $oldQtt);
            } elseif($newQtt < $oldQtt) {
                $newQttRestant = $oldQttRestant - ($oldQtt - $newQtt);
            }
            $stock->setQttRestant($newQttRestant);


        }
        
    
        // Calculez le nouveau stock restant après soustraction de l'ancienne quantité
        if ($oldQtt <= $oldStockRestant) {
            $stockRestant = $oldStockRestant - $oldQtt;
            $newQtt = $stock->getQtt();
            $stockRestant = $stockRestant + $newQtt;
    
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
    
            if ($newDatePeremption) {
                $stock->setDatePeremption($newDatePeremption);
            }
            
            // Persist l'état actuel de stock
            $this->entityManager->persist($stock);
    
            // Obtenir l'utilisateur connecté
            $user = $this->security->getUser();
    
            // Créer log
            $this->logger->info('Stock de produit catégorie modifié', [
                'Produit' => $produitCategorie->getNom(),
                'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                'ID Application' => $produitCategorie->getApplication()->getId()
            ]);
    
            $this->update();
            
            return $stock;
        } else {
            $stocktoAdd = $oldQtt - $oldStockRestant;
            $stockRestant = $oldStockRestant + $stocktoAdd;
    
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
    
            if ($newDatePeremption) {
                $stock->setDatePeremption($newDatePeremption);
            }
            // Persist l'état actuel de stock
            $this->entityManager->persist($stock);
    
            // Obtenir l'utilisateur connecté
            $user = $this->security->getUser();
    
            // Créer log
            $this->logger->info('Stock de produit catégorie modifié', [
                'Produit' => $produitCategorie->getNom(),
                'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                'ID Application' => $produitCategorie->getApplication()->getId()
            ]);
            
            $this->update();
        }
        return $stock;
    }

    /*public function edit($stock, $produitCategorie, $oldQtt, $datePeremption)
    {
        // Obtenez la quantité et le stock restant actuels
        $oldStockRestant = $produitCategorie->getStockRestant();
    
        // Initialisation de $newDatePeremption
        $newDatePeremption = null;
    
        // Obtenez l'ID de la date de péremption actuelle du stock
        $datePeremptionId = $stock->getDatePeremption() ? $stock->getDatePeremption()->getId() : null;
    
        // Vérifiez si une nouvelle date de péremption est fournie
        if ($datePeremption != null) {
            if ($datePeremptionId == null) {
                // Créez une nouvelle DatePeremption si elle n'existe pas déjà
                $newDatePeremption = new DatePeremption();
                $newDatePeremption->setDate($datePeremption);
                $newDatePeremption->setDateCreation(new \DateTime());
                $this->entityManager->persist($newDatePeremption);
            } else {
                // Si la date de péremption existe déjà, récupérez-la
                $newDatePeremption = $this->entityManager->getRepository(DatePeremption::class)->find($datePeremptionId);
                $newDatePeremption->setDate($datePeremption);
            }
        }
    
        // Calculez le nouveau stock restant après soustraction de l'ancienne quantité
        if ($oldQtt <= $oldStockRestant) {
            $stockRestant = $oldStockRestant - $oldQtt;
            $newQtt = $stock->getQtt();
            $stockRestant = $stockRestant + $newQtt;
    
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
    
            if ($newDatePeremption) {
                $stock->setDatePeremption($newDatePeremption);
            }
            
            // Persist l'état actuel de stock
            $this->entityManager->persist($stock);
    
            // Obtenir l'utilisateur connecté
            $user = $this->security->getUser();
    
            // Créer log
            $this->logger->info('Stock de produit catégorie modifié', [
                'Produit' => $produitCategorie->getNom(),
                'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                'ID Application' => $produitCategorie->getApplication()->getId()
            ]);
    
            $this->update();
            
            return $stock;
        } else {
            $stocktoAdd = $oldQtt - $oldStockRestant;
            $stockRestant = $oldStockRestant + $stocktoAdd;
    
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
    
            if ($newDatePeremption) {
                $stock->setDatePeremption($newDatePeremption);
            }
            // Persist l'état actuel de stock
            $this->entityManager->persist($stock);
    
            // Obtenir l'utilisateur connecté
            $user = $this->security->getUser();
    
            // Créer log
            $this->logger->info('Stock de produit catégorie modifié', [
                'Produit' => $produitCategorie->getNom(),
                'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                'ID Application' => $produitCategorie->getApplication()->getId()
            ]);
            
            $this->update();
        }
        return $stock;
    }*/
    

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($stock, $produitCategorie)
    {
        $stockRestant = $produitCategorie->getStockRestant();

        $qtt = $stock->getQtt();

        $stockProduit = $stockRestant - $qtt;
            
        $this->entityManager->remove($stock);

        $produitCategorie->setStockRestant($stockProduit);

        $this->entityManager->persist($produitCategorie);

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer log
        $this->logger->info('Stock de produit catégorie supprimé', [
            'Produit' => $produitCategorie->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $produitCategorie->getApplication()->getId()
        ]);

        $this->update();
    }

    public function getAllStocks()
    {
        $stocks = $this->entityManager->getRepository(Stock::class)->findAll();
        if (count($stocks) > 0) {
            return $stocks;
        }
        return false;
    }

    public function getStockById($id)
    {
        $stock = $this->entityManager->getRepository(Stock::class)->find($id);
        if ($stock) {
            return $stock;
        }
        return null;
    }

    public function getStockByProduit($produitCategorie)
    {
        $stock = $this->entityManager->getRepository(Stock::class)->findByProductCategory($produitCategorie);
        if ($stock) {
            return $stock;
        }
        return null;
    }

    public function getQuantiteVenduByReferenceProduit($reference)
    {
        $stock = $this->entityManager->getRepository(FactureDetail::class)->getProduitsVenduByReference($reference);
        if ($stock) {
            return $stock;
        }
        return false;
    }

}