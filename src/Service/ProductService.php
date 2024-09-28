<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\FactureDetail;
use App\Entity\Transfert;
use App\Entity\ProduitType;
use App\Entity\Notification;
use App\Entity\Product;
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
use App\Repository\ProductRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProductService
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

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        CategorieRepository $categorieRepo,
        ProduitTypeRepository $typeRepo,
        ProductImageRepository $imageRepo,
        CompteRepository $compteRepo)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->categorieRepo = $categorieRepo;
        $this->typeRepo = $typeRepo;
        $this->imageRepo = $imageRepo;
        $this->compteRepo = $compteRepo;
    }

    public function add($instance, $affaire, $data = [])
    {
        $stock = $this->entityManager->getRepository(Stock::class)->findOneByProduitCategorie($instance);
        
        $datePeremption = null;
        
        if ($stock !== null) {
            // Si le stock existe, vérifier si la date de péremption est définie
            $datePeremption = $stock->getDatePeremption();
        
            if ($datePeremption !== null) {
                // Récupérer la date de péremption si elle existe
                $datePeremption = $datePeremption->getDate();
            } else {
                // Gérer le cas où la date de péremption est nulle
                $datePeremption = null; // ou définir une valeur par défaut, selon vos besoins
            }
        } else {
            // Gérer le cas où aucun stock n'est trouvé
            $datePeremption = null; // ou définir une valeur par défaut, selon vos besoins
        }

        $product = new Product();

        $date = new \DateTime();
        $product->setDatePeremption($datePeremption);
        $product->setNom($instance->getNom());
        $product->setProduitCategorie($instance);
        $product->setApplication($instance->getApplication());
        $product->setDateCreation($date);
        $product->setDescription($instance->getDescription());
        $product->setReference($instance->getReference());
        $product->setPuHt($instance->getPrixHt());
        $product->setTva($instance->getTva());
        $product->setQtt($data['qtt']);
        $product->setTypeVente($data['typeVente']);
        /*$product->setStockRestant($instance->getStockRestant());
        $product->setStockMin($instance->getStockMin());
        $product->setStockMax($instance->getStockMax());*/
        $product->setUniteVenteGros($instance->getUniteVenteGros());
        $product->setUniteVenteDetail($instance->getUniteVenteDetail());
        $product->setPrixVenteGros($instance->getPrixVenteGros());
        $product->setPrixVenteDetail($instance->getPrixVenteDetail());
        $product->setPrixTTC($instance->getPrixTTC());
        $product->setPrixAchat($instance->getPrixAchat());
        $product->addAffaire($affaire);

        $this->entityManager->persist($product);

        $qttProduct = $product->getQtt();

        $qttReserverGros = $instance->getQttReserverGros();
        $qttReserverDetail = $instance->getQttReserverDetail();

        if($qttReserverGros != null) {
            $qttReserverGros = $qttReserverGros + $qttProduct;
        } else {
            $qttReserverGros = $qttProduct;
        }

        if($qttReserverDetail != null) {
            $qttReserverDetail = $qttReserverDetail + $qttProduct;
        } else {
            $qttReserverDetail = $qttProduct;
        }

        if($product->getTypeVente() == "gros") {
            $instance->setQttReserverGros($qttReserverGros);
        }elseif($product->getTypeVente() == "detail") {
            $instance->setQttReserverDetail($qttReserverDetail);
        }

        $this->entityManager->persist($instance);

       $this->update();
        unset($instance);
        return $product;
    }

    /*public function setQttReserver($product, $qtt, $isDeleteQttReserver = false) 
    {
        $qttReserver = $product->getProduitCategorie()->getQttReserver();
        
        if (!$isDeleteQttReserver) {
            $oldQtt = $product->getQtt();
            $product->setQtt(floatval($qtt));
           
            if (null != $qtt && "" != $qtt) {
                $qtt = floatval($qtt);
            }
            if ($product->getTypeVente() == "detail" && $product->getProduitCategorie()->getVolumeGros() > 0) {
                $oldQtt = round($oldQtt / $product->getProduitCategorie()->getVolumeGros(), 2);
                $qtt = round($qtt / $product->getProduitCategorie()->getVolumeGros(), 2);
            }

            $difference = 0;
            $isAddInReserve = false;
            if ($qtt < $oldQtt) {
                $difference = $oldQtt - $qtt;
                $isAddInReserve = false;
            }
            if ($qtt > $oldQtt) {
                $difference = $qtt - $oldQtt;
                $isAddInReserve = true;
            }

            if ($qtt == $oldQtt) { 
                $isAddInReserve = null;
            }
           
         
            if (!is_null($isAddInReserve)) {
                $difference = number_format($difference,2,'.','');
                $qttReserver = number_format($qttReserver,2,'.','');
                $product->getProduitCategorie()->setQttReserver($qttReserver - $difference);
                if ($isAddInReserve) {
                    $product->getProduitCategorie()->setQttReserver($qttReserver + $difference);
                } 
            } 

            
        } else {
            if ($product->getTypeVente() == "detail" && $product->getProduitCategorie()->getVolumeGros() > 0) {
                $qtt = round($qtt / $product->getProduitCategorie()->getVolumeGros(), 2);
            }
            
            $product->getProduitCategorie()->setQttReserver($qttReserver - $qtt);
            $this->persist($product->getProduitCategorie());
        }
        
    }*/

    public function setQttReserver($product, $qtt, $isDeleteQttReserver = false) 
    {
        $qttReserverGros = $product->getProduitCategorie()->getQttReserverGros();
        $qttReserverDetail = $product->getProduitCategorie()->getQttReserverDetail();
        
        if (!$isDeleteQttReserver) {
            $oldQtt = $product->getQtt();
            $product->setQtt(floatval($qtt));
           
            if (null != $qtt && "" != $qtt) {
                $qtt = floatval($qtt);
            }

            $difference = 0;
            $isAddInReserve = false;
            if ($qtt < $oldQtt) {
                $difference = $oldQtt - $qtt;
                $isAddInReserve = false;
            }
            if ($qtt > $oldQtt) {
                $difference = $qtt - $oldQtt;
                $isAddInReserve = true;
            }

            if ($qtt == $oldQtt) { 
                $isAddInReserve = null;
            }
           
         
            if (!is_null($isAddInReserve)) {
                if($product->getTypeVente() == "gros") {
                    $product->getProduitCategorie()->setQttReserverGros($qttReserverGros - $difference);
                    if ($isAddInReserve) {
                        $product->getProduitCategorie()->setQttReserverGros($qttReserverGros + $difference);
                    } 
                } elseif($product->getTypeVente() == "detail") {
                    $product->getProduitCategorie()->setQttReserverDetail($qttReserverDetail - $difference);
                    if ($isAddInReserve) {
                        $product->getProduitCategorie()->setQttReserverDetail($qttReserverDetail + $difference);
                    } 
                }
                
            } 

            
        } else {

            if($product->getTypeVente() == "gros") {
                $product->getProduitCategorie()->setQttReserverGros($qttReserverGros - $qtt);
            }elseif($product->getTypeVente() == "detail") {
                $product->getProduitCategorie()->setQttReserverDetail($qttReserverDetail - $qtt);
            }
        }

        $this->persist($product->getProduitCategorie());
        //dd($product->getProduitCategorie()->getQttReserverGros(), $product->getProduitCategorie()->getQttReserverDetail());
        
    }

    public function persist($entity)
    {
        $this->entityManager->persist($entity);
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove($product, $affaire)
    {
        $factureDetail = $this->getFactureDetailByProduct($product->getId());
        if ($factureDetail != false) {
            $this->entityManager->remove($factureDetail);
        }
        $affaire->removeProduct($product);
        
        $this->persist($affaire);

        $this->entityManager->remove($product);
        $this->update();
    }

    public function getFactureDetailByProduct($productId)
    {
        $factureDetail = $this->entityManager->getRepository(FactureDetail::class)->find($productId);
        
        if ($factureDetail != null) {
            return $factureDetail;
        }
        return false;
    }

    public function getAllProduitCategories()
    {
        $products = $this->entityManager->getRepository(ProduitCategorie::class)->getProduits();
        if ($products != false  && count($products) > 0) {
            return $products;
        }
        return false;
    }

    public function getProductById($id)
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        if ($product) {
            return $product;
        }
        return false;
    }

    public function findProduitAffaire($affaire)
    {
        $produits = $this->entityManager->getRepository(Product::class)->findProduitAffaire($affaire);
        if (count($produits) > 0) {
            return $produits;
        }
        return false;
    }

    public function getAllFournisseur()
    {
        $product = $this->entityManager->getRepository(ProduitCategorie::class)->getAllFournisseur();
        if ($product) {
            return $product;
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

    public function addTransfert($product, $newApplication, $quantity)
    {
        $transfert = new Transfert();
        $transfert->setProduitCategorie($product);
        $transfert->setOldApplication($product->getApplication());
        $transfert->setApplication($newApplication);
        $transfert->setQuantity($quantity);
        $transfert->setDateCreation(new \DateTime());

        $this->entityManager->persist($transfert);
    }

    
    public function updateStockRestant($oldProduitCategorie, $quantity)
    {
        $oldStockRestant = $oldProduitCategorie->getStockRestant();
        $newStockRestant = $oldStockRestant - $quantity;
        
        if ($newStockRestant < 0) {
            $oldProduitCategorie->setStockRestant(0);
        } else {
            $oldProduitCategorie->setStockRestant($newStockRestant);
        }
        
        $this->entityManager->persist($oldProduitCategorie);

        
    }

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

        $stock = new Stock();
        $stock->setProduitCategorie($newProduitCategorie);
        $stock->setQtt($quantity);
        $stock->setDateCreation($date);
        $this->entityManager->persist($stock);

        foreach ($oldProduitCategorie->getComptes() as $compte) {
            $existingCompte = $this->compteRepo->findOneBy(['nom' => $compte->getNom(), 'application' => $application]);
            $newCompte = $existingCompte ?: $this->createNewCompte($compte, $application);
            $newProduitCategorie->addCompte($newCompte);
            $compte->addProduitCategory($newProduitCategorie);
            $this->entityManager->persist($newCompte);
        }

        $this->entityManager->persist($newProduitCategorie);
        $this->update();

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

    
}
