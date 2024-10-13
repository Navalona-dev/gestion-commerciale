<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\Transfert;
use App\Entity\ProduitType;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use App\Entity\ProduitCategorie;
use App\Repository\CompteRepository;
use App\Service\AuthorizationManager;
use App\Repository\CategorieRepository;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitTypeRepository;
use App\Exception\ActionInvalideException;
use App\Repository\ProductImageRepository;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProduitCategorieService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $session;
    public  $isCurrentDossier = false;
    private $categorieRepo;
    private $typeRepo;
    private $imageRepo;
    private $compteRepo;
    private $logger;
    private $security;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        CategorieRepository $categorieRepo,
        ProduitTypeRepository $typeRepo,
        ProductImageRepository $imageRepo,
        CompteRepository $compteRepo,
        LoggerInterface $productLogger, 
        Security $security)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->categorieRepo = $categorieRepo;
        $this->typeRepo = $typeRepo;
        $this->imageRepo = $imageRepo;
        $this->compteRepo = $compteRepo;
        $this->logger = $productLogger;
        $this->security = $security;
    }

    public function add($instance, $application = null, $idFournisseur = null, $formData = null)
    {
        $produitCategorie = null;
        
        $error = false;

        $categorieName = $formData->getCategorie();
        $typeName = $formData->getType();
        $reference = $formData->getReference();

        $categorie = null;
        $type = null;

        // Vérifier si la référence existe déjà pour l'application
        $existingProduitCategorie = $this->entityManager->getRepository(ProduitCategorie::class)
        ->findOneBy([
            'reference' => $reference,
            'application' => $application,
        ]);
       
        if ($existingProduitCategorie) {
            $error = true;
        } else {

            $produitCategorie = ProduitCategorie::newProduitCategorie($instance);

            $date = new \DateTime();
            
            if($categorieName) {
                $categorie = $categorieName;
                $produitCategorie->setCategorie($categorie);
            } else {
                $categorie = $this->categorieRepo->findOneBy(['nom' => 'Autre']);
                if(!$categorie){
                    $newCategorie = new Categorie();
                    $newCategorie->setNom('Autre');
                    $newCategorie->setDateCreation(new \DateTime());
                    $newCategorie->setApplication($application);
                    $this->entityManager->persist($newCategorie);
                    $produitCategorie->setCategorie($newCategorie);
                } else {
                    $produitCategorie->setCategorie($categorie);
                }
            }
            
            if($typeName) {
                $type = $typeName;
                $produitCategorie->setType($type);
            } else {
                $type = $this->typeRepo->findOneBy(['nom' => 'Autre']);
                if(!$type){
                    $newType = new ProduitType();
                    $newType->setNom('Autre');
                    $newType->setDateCreation(new \DateTime());
                    $newType->setApplication($application);
                    $this->entityManager->persist($newType);
                    $produitCategorie->setType($newType);
                } else {
                    $produitCategorie->setType($type);
                }
            }
           
            if(isset($idFournisseur) && !empty($idFournisseur)) {

                $fournisseur = $this->getFournisseurById($idFournisseur);
                if ($fournisseur != false && $fournisseur != null) {
                    $produitCategorie->addCompte($fournisseur);
                    $fournisseur->addProduitCategory($produitCategorie);
                    $this->persist($fournisseur);

                    $codeFournisseur = $fournisseur->getCode();
                    if($codeFournisseur) {
                        //$produitCategorie->setReference($codeFournisseur . '' . $produitCategorie->getReference());
                    }
                } 
            } else {
                $produitCategorie->setReference($reference);
            }

            //$produitCategorie->setQtt($produitCategorie->getStockRestant());
            $produitCategorie->setApplication($application);

            $produitCategorie->setDateCreation($date);
            $produitCategorie->setDescription($instance->getDescription());
            $produitCategorie->setPrixHt($instance->getPrixHt());
            $produitCategorie->setTva($instance->getTva());
            $produitCategorie->setStockRestant($instance->getStockRestant());
            $produitCategorie->setStockMin(10);
            $produitCategorie->setStockMax(50);
            $produitCategorie->setUniteVenteGros($instance->getUniteVenteGros());
            $produitCategorie->setUniteVenteDetail($instance->getUniteVenteDetail());
            $produitCategorie->setPrixVenteGros($instance->getPrixVenteGros());
            $produitCategorie->setPrixVenteDetail($instance->getPrixVenteDetail());
            $produitCategorie->setPrixTTC($instance->getPrixTTC());
            $produitCategorie->setPrixAchat($instance->getPrixAchat());

            foreach($produitCategorie->getProductImages() as $productImage) {
                $productImage->setProduitCategorie($produitCategorie);
                $productImage->setDateCreation($date);
                $this->entityManager->persist($productImage);
            }

            $stock = new Stock();

            if($produitCategorie->getStockRestant()) {
                $qtt = $produitCategorie->getStockRestant();
            } else {
                $qtt = 0;
            }

            $stock->setQtt($qtt);
            $stock->setQttRestant($qtt);
            $stock->setProduitCategorie($produitCategorie);
            $stock->setDateCreation($date);

            $this->entityManager->persist($stock);

            $this->entityManager->persist($produitCategorie);

            // Obtenir l'utilisateur connecté
            $user = $this->security->getUser();

            // Créer log
            $this->logger->info('Produit catégorie ajouté', [
                'Produit' => $produitCategorie->getNom(),
                'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                'ID Application' => $produitCategorie->getApplication()->getId()
            ]);

            
            $this->update();

            unset($instance);
        }

        return [$produitCategorie, $error];
    }

    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($produitCategorie)
    {
        $stocks = $produitCategorie->getStocks();

        foreach($stocks as $stock) {
            if ($stock->getDatePeremption() != null) {
                $this->entityManager->remove($stock->getDatePeremption());
            } 
            $this->entityManager->remove($stock);
        }

        $images = $produitCategorie->getProductImages();

        foreach($images as $image) {
            $this->entityManager->remove($image);
        }

        $transferts = $produitCategorie->getTransferts();

        foreach($transferts as $transfert) {
            $this->entityManager->remove($transfert);
        }

        $notifications = $produitCategorie->getNotifications();

        foreach($notifications as $notification) {
            $this->entityManager->remove($notification);
        }

        $this->entityManager->remove($produitCategorie);
        
         // Obtenir l'utilisateur connecté
         $user = $this->security->getUser();

         // Créer log
         $this->logger->info('Produit catégorie supprimé', [
             'Produit' => $produitCategorie->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
             'ID Application' => $produitCategorie->getApplication()->getId()
         ]);

        $this->update();
    }

    public function removeMultiple(array $produitCategories)
    {
        foreach ($produitCategories as $produitCategorie) {
            $stocks = $produitCategorie->getStocks();

            foreach($stocks as $stock) {
                if ($stock->getDatePeremption() != null) {
                    $this->entityManager->remove($stock->getDatePeremption());
                } 
                $this->entityManager->remove($stock);
            }

            $images = $produitCategorie->getProductImages();

            foreach($images as $image) {
                $this->entityManager->remove($image);
            }

            $transferts = $produitCategorie->getTransferts();

            foreach($transferts as $transfert) {
                $this->entityManager->remove($transfert);
            }

            $notifications = $produitCategorie->getNotifications();

            foreach($notifications as $notification) {
                $this->entityManager->remove($notification);
            }

            $products = $produitCategorie->getProduits();
            foreach($products as $product) {
                $affaires = $product->getAffaires();
                foreach($affaires as $affaire) {
                    $factures = $affaire->getFactures();
                    foreach($factures as $facture) {
                        $factureDetails = $facture->getFactureDetails();
                        foreach($factureDetails as $factureDetail) {
                            $this->entityManager->remove($factureDetail);
                        }
                        $methodePaiements = $facture->getMethodePaiements();
                        foreach($methodePaiements as $methodePaiement) {
                            $this->entityManager->remove($methodePaiement);
                        }
                        $factureEcheances = $facture->getFactureEcheances();
                        foreach($factureEcheances as $factureEcheance) {
                            $this->entityManager->remove($factureEcheance);
                        }
                        $this->entityManager->remove($facture);
                    }
                    $this->entityManager->remove($affaire);

                }

                $dateperemptions = $product->getDatePeremptionProducts();
                foreach($dateperemptions as $dateperemption) {
                    $this->entityManager->remove($dateperemption);

                }

                $this->entityManager->remove($product);
            }

            $this->entityManager->remove($produitCategorie);
        
            // Obtenir l'utilisateur connecté
            $user = $this->security->getUser();

            // Créer log
            $this->logger->info('Produit catégorie supprimé', [
                'Produit' => $produitCategorie->getNom(),
                'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
                'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
                'ID Application' => $produitCategorie->getApplication()->getId()
            ]);

        }

        $this->update();  
    }


    public function getAllProduitCategories($affairesProduct = [])
    {
        $produitCategories = $this->entityManager->getRepository(ProduitCategorie::class)->getProduits($affairesProduct);
        if ($produitCategories != false  && count($produitCategories) > 0) {
            return $produitCategories;
        }
        return false;
    }

    public function getAllProduitCategoriesByStockRestant($affairesProduct = [])
    {
        $produitCategories = $this->entityManager->getRepository(ProduitCategorie::class)->getProduitsByStockRestant($affairesProduct);
        if ($produitCategories != false  && count($produitCategories) > 0) {
            return $produitCategories;
        }
        return false;
    }

    public function getCategorieById($id)
    {
        $produitCategorie = $this->entityManager->getRepository(ProduitCategorie::class)->find($id);
        if ($produitCategorie) {
            return $produitCategorie;
        }
        return false;
    }

    public function getAllFournisseur()
    {
        $produitCategorie = $this->entityManager->getRepository(ProduitCategorie::class)->getAllFournisseur();
        if ($produitCategorie) {
            return $produitCategorie;
        }
        return false;
    }

    public function getFournisseurById($id)
    {
        $compte = $this->entityManager->getRepository(Compte::class)->find($id);
        if ($compte) {
            return $compte;
        }
        return false;
    }

    public function addTransfert($produitCategorie, $newApplication, $quantity)
    {
        $transfert = new Transfert();
        $transfert->setProduitCategorie($produitCategorie);
        $transfert->setOldApplication($produitCategorie->getApplication());
        $transfert->setApplication($newApplication);
        $transfert->setQuantity($quantity);
        $transfert->setDateCreation(new \DateTime());

        $this->entityManager->persist($transfert);
    }

    
    public function updateStockRestant($oldProduitCategorie, $quantity, $application)
    {
        $oldStockRestant = $oldProduitCategorie->getStockRestant();
        $newStockRestant = $oldStockRestant - $quantity;
        
        if ($newStockRestant < 0) {
            $oldProduitCategorie->setStockRestant(0);
        } else {
            $oldProduitCategorie->setStockRestant($newStockRestant);
        }
        
        $this->entityManager->persist($oldProduitCategorie);

        if($oldProduitCategorie->getStockRestant() <= $oldProduitCategorie->getStockMin()) {
            $this->createNotification($application, "Le stock du produit " . $oldProduitCategorie->getNom() . " est presque épuisé, veuillez ajouter un ou plusieurs" , $oldProduitCategorie);
        }
        
    }

    /*public function addNewProductForNewApplication($productReferenceExists, $oldProduitCategorie, $quantity, $application, $isChangePrice)
    {
        $newProduitCategorie = $productReferenceExists ? $productReferenceExists : new ProduitCategorie();
        $date = new \DateTime();
        $categorie = $oldProduitCategorie->getCategorie();
        $type = $oldProduitCategorie->getType();

        $existingCategorie = $this->categorieRepo->findOneBy(['nom' => $categorie->getNom(), 'application' => $application]);
        $existingType = $this->typeRepo->findOneBy(['nom' => $type->getNom(), 'application' => $application]);

        $newProduitCategorie->setCategorie($existingCategorie ?: $this->createNewCategorie($categorie, $application));
        $newProduitCategorie->setType($existingType ?: $this->createNewType($type, $application));

        $newProduitCategorie->setNom($oldProduitCategorie->getNom())
            ->setApplication($application)
            ->setReference($oldProduitCategorie->getReference())
            ->setTva($oldProduitCategorie->getTva())
            ->setQtt($oldProduitCategorie->getQtt())
            ->setStockMin($oldProduitCategorie->getStockMin())
            ->setStockMax($oldProduitCategorie->getStockMax())
            ->setUniteVenteGros($oldProduitCategorie->getUniteVenteGros())
            ->setUniteVenteDetail($oldProduitCategorie->getUniteVenteDetail())
            ->setPrixVenteGros($oldProduitCategorie->getPrixVenteGros())
            ->setPrixVenteDetail($oldProduitCategorie->getPrixVenteDetail())
            ->setPrixTTC($oldProduitCategorie->getPrixTTC())
            ->setPrixAchat($oldProduitCategorie->getPrixAchat())
            ->setPrixHt($oldProduitCategorie->getPrixHt())
            ->setDateCreation($date);

        foreach ($oldProduitCategorie->getProductImages() as $productImage) {
            $productImage->setProduitCategorie($newProduitCategorie);
            $productImage->setDateCreation($date);
            $this->entityManager->persist($productImage);
        }

        foreach ($oldProduitCategorie->getComptes() as $compte) {
            $existingCompte = $this->compteRepo->findOneBy(['nom' => $compte->getNom(), 'application' => $application]);
            $newCompte = $existingCompte ?: $this->createNewCompte($compte, $application);
            $newProduitCategorie->addCompte($newCompte);
            $compte->addProduitCategory($newProduitCategorie);
            $this->entityManager->persist($newCompte);
        }

        $stock = new Stock();
        $stock->setProduitCategorie($newProduitCategorie);
        $stock->setQtt($quantity);
        $stock->setDateCreation($date);
        $this->entityManager->persist($stock);

        $this->entityManager->persist($newProduitCategorie);

        if ($isChangePrice) {
            $newProduitCategorie->setIsChangePrix(true);
            $this->createNotification($application, "Le prix du produit transféré doit être modifié en raison des nouvelles conditions d'application.", $newProduitCategorie);
        }

        // Calcul du stock total
        $totalQtt = 0;
        $stocks = $newProduitCategorie->getStocks();
        foreach ($stocks as $stck) {
            $qtt = $stck->getQtt();
            $totalQtt += $qtt;
        }
        $totalQtt = intval($totalQtt);

        // Calcul du total des transferts
        $totalQttTransfert = 0;
        $transferts = $newProduitCategorie->getTransferts();
        foreach ($transferts as $transfert) {
            $qttTransfert = $transfert->getQuantity();
            $totalQttTransfert += $qttTransfert;
        }
        $totalQttTransfert = intval($totalQttTransfert);

        // Calcul du stock restant final
        $totalQttFinal = $totalQtt - $totalQttTransfert;

        // Mise à jour du stock restant en fonction des conditions
        if (count($stocks) == 0) {
            $newProduitCategorie->setStockRestant($quantity); // Cas où aucun stock existait
        } elseif (count($stocks) > 0 && count($transferts) > 0) {
            $newProduitCategorie->setStockRestant($totalQttFinal); // Cas où il y a des stocks et plusieurs transferts
        } else {
            $newProduitCategorie->setStockRestant($totalQtt); // Cas par défaut
        }

        $this->entityManager->persist($newProduitCategorie);

         // Obtenir l'utilisateur connecté
         $user = $this->security->getUser();

         // Créer log
         $this->logger->info('Produit catégorie transféré', [
             'Produit' => $oldProduitCategorie->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
             'ID Application' => $oldProduitCategorie->getApplication()->getId()
         ]);

        $this->update();

    }*/

    public function addNewProductForNewApplication($productReferenceExists, $oldProduitCategorie, $quantity, $application, $isChangePrice)
    {
        $newProduitCategorie = $productReferenceExists ? $productReferenceExists : new ProduitCategorie();
        $date = new \DateTime();
        $categorie = $oldProduitCategorie->getCategorie();
        $type = $oldProduitCategorie->getType();

        $existingCategorie = $this->categorieRepo->findOneBy(['nom' => $categorie->getNom(), 'application' => $application]);
        $existingType = $this->typeRepo->findOneBy(['nom' => $type->getNom(), 'application' => $application]);

        $newProduitCategorie->setCategorie($existingCategorie ?: $this->createNewCategorie($categorie, $application));
        $newProduitCategorie->setType($existingType ?: $this->createNewType($type, $application));

        $newProduitCategorie->setNom($oldProduitCategorie->getNom())
            ->setApplication($application)
            ->setReference($oldProduitCategorie->getReference())
            ->setTva($oldProduitCategorie->getTva())
            ->setQtt($oldProduitCategorie->getQtt())
            ->setStockMin($oldProduitCategorie->getStockMin())
            ->setStockMax($oldProduitCategorie->getStockMax())
            ->setUniteVenteGros($oldProduitCategorie->getUniteVenteGros())
            ->setUniteVenteDetail($oldProduitCategorie->getUniteVenteDetail())
            ->setPrixVenteGros($oldProduitCategorie->getPrixVenteGros())
            ->setPrixVenteDetail($oldProduitCategorie->getPrixVenteDetail())
            ->setPrixTTC($oldProduitCategorie->getPrixTTC())
            ->setPrixAchat($oldProduitCategorie->getPrixAchat())
            ->setPrixHt($oldProduitCategorie->getPrixHt())
            ->setDateCreation($date);

        
            foreach ($oldProduitCategorie->getProductImages() as $productImage) {
            $productImage->setProduitCategorie($newProduitCategorie);
            $productImage->setDateCreation($date);
            $this->entityManager->persist($productImage);
        }

        foreach ($oldProduitCategorie->getComptes() as $compte) {
            $existingCompte = $this->compteRepo->findOneBy(['nom' => $compte->getNom(), 'application' => $application]);
            $newCompte = $existingCompte ?: $this->createNewCompte($compte, $application);
            $newProduitCategorie->addCompte($newCompte);
            $compte->addProduitCategory($newProduitCategorie);
            $this->entityManager->persist($newCompte);
        }

        // Copie des stocks de l'ancienne application
        $stocksOldProduitCategorie = $oldProduitCategorie->getStocks();
        $stockArray = $stocksOldProduitCategorie->toArray();

        // Trier les stocks de l'ancienne application par quantité restante
        usort($stockArray, function($a, $b) {
            return $b->getQttRestant() <=> $a->getQttRestant();
        });

        $newStockRestantNewProduitCategorie = 0;

        // Transférer la quantité en plusieurs stocks avec date de péremption
        foreach ($stockArray as $oldStock) {
            if ($quantity <= 0) {
                break;
            }

            $qttRestant = $oldStock->getQttRestant();

            if ($qttRestant > 0) {
                $toTransfer = min($quantity, $qttRestant);

                // Créer un nouveau stock pour la nouvelle application
                $newStock = new Stock();
                $newStock->setProduitCategorie($newProduitCategorie);
                $newStock->setQtt($toTransfer);
                $newStock->setDateCreation($date);
                $newStock->setDatePeremption($oldStock->getDatePeremption()); // Conserver la date de péremption
                $newStock->setQttRestant($toTransfer);

                // Persist le nouveau stock
                $this->entityManager->persist($newStock);

                $newStockRestantNewProduitCategorie += $newStock->getQttRestant();

                // Réduire la quantité à transférer
                $quantity -= $toTransfer;

                // Mettre à jour le stock restant dans l'ancienne application
                $oldStock->setQttRestant($qttRestant - $toTransfer);
                $this->entityManager->persist($oldStock);
            }
        }

        $newProduitCategorie->setStockRestant($newProduitCategorie->getStockRestant() + $newStockRestantNewProduitCategorie);

        $this->entityManager->persist($newProduitCategorie);

        if ($isChangePrice) {
            $newProduitCategorie->setIsChangePrix(true);
            $this->createNotification($application, "Le prix du produit transféré doit être modifié en raison des nouvelles conditions d'application.", $newProduitCategorie);
        }


         // Obtenir l'utilisateur connecté
         $user = $this->security->getUser();

         // Créer log
         $this->logger->info('Produit catégorie transféré', [
             'Produit' => $oldProduitCategorie->getNom(),
             'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
             'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
             'ID Application' => $oldProduitCategorie->getApplication()->getId()
         ]);

        $this->update();

    }

    private function createNewCompte($compte, $application)
    {
        $newCompte = new Compte();
        $newCompte->setNom($compte->getNom());
        $newCompte->setGenre(2);
        $newCompte->setEtat($compte->getEtat() ?: null);
        $newCompte->setStatut($compte->getStatut() ?: null);
        $newCompte->setEmail($compte->getEmail() ?: null);
        $newCompte->setTelephone($compte->getTelephone() ?: null);
        $newCompte->setApplication($application);
        $newCompte->setDateCreation(new \DateTime());

        $this->entityManager->persist($newCompte);
        return $newCompte;
    }

    private function createNewCategorie($categorie, $application)
    {
        $newCategorie = new Categorie();
        $newCategorie->setNom($categorie->getNom());
        $newCategorie->setApplication($application);
        $newCategorie->setDateCreation(new \DateTime());

        $this->entityManager->persist($newCategorie);
        return $newCategorie;
    }

    private function createNewType($type, $application)
    {
        $newType = new ProduitType();
        $newType->setNom($type->getNom());
        $newType->setDescription($type->getDescription() ?: null);
        $newType->setApplication($application);
        $newType->setIsActive(true);
        $newType->setDateCreation(new \DateTime());

        $this->entityManager->persist($newType);
        return $newType;
    }

    private function createNotification($application, $message, $newProduitCategorie)
    {
        $notification = new Notification();
        $notification->setApplication($application);
        $notification->setMessage($message);
        $notification->setDateCreation(new \DateTime());
        $notification->setProduitCategorie($newProduitCategorie);

        $this->entityManager->persist($notification);
        return $notification;
    }


    public function getAllProduitByCompteAndApplication($compte, $application)
    {
        return $this->entityManager->getRepository(ProduitCategorie::class)->findProductsByCompteAndApplication($compte, $application);
    }

    public function getAllProduitDatePeremption()
    {
        return $this->entityManager->getRepository(ProduitCategorie::class)->produitDatePeremptionProche();
    }

    
}
