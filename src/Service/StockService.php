<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Compte;
use App\Entity\Categorie;
use App\Entity\Transfert;
use App\Entity\ProduitType;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Entity\FactureDetail;
use App\Entity\DatePeremption;
use Doctrine\ORM\EntityManager;
use App\Entity\ProduitCategorie;
use App\Repository\StockRepository;
use App\Service\ApplicationManager;
use App\Repository\CompteRepository;
use App\Service\AuthorizationManager;
use App\Repository\CategorieRepository;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitTypeRepository;
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
    private $stockRepository;
    private $categorieRepo;
    private $typeRepo;
    private $compteRepo;

    public function __construct(
        ApplicationManager  $applicationManager, 
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        LoggerInterface $productLogger, 
        Security $security,
        StockRepository $stockRepository,
        CategorieRepository $categorieRepo,
        ProduitTypeRepository $typeRepo,
        CompteRepository $compteRepo
        )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
        $this->logger = $productLogger;
        $this->security = $security;
        $this->stockRepository = $stockRepository;
        $this->categorieRepo = $categorieRepo;
        $this->typeRepo = $typeRepo;
        $this->compteRepo = $compteRepo;

    }

    public function add($instance, $produitCategorie, $datePeremption)
    {
        $date = new \DateTime();

        $formattedDatePeremption = $datePeremption ? $datePeremption->format('d-m-Y') : '';

        $stocks = $this->entityManager->getRepository(Stock::class)->findBy(['produitCategorie' => $produitCategorie]);
        $existingStock = null;

        // Trouver un stock existant avec la même date de péremption
        foreach ($stocks as $stock) {
            if($stock->getDatePeremption()) {
                $formattedDatePeremptionStock = $stock->getDatePeremption()->getDate()->format('d-m-Y');
                if ($formattedDatePeremption && $formattedDatePeremptionStock === $formattedDatePeremption) {
                    $existingStock = $stock;
                    break; 
                }
            }
        }

        $stockProduit = ($produitCategorie->getStockRestant() === null) ? 0 : $produitCategorie->getStockRestant();

        $stockMax = $produitCategorie->getStockMax();

        $newStock = null;
        $stockRestant = null;

        if ($existingStock) {
            // Mettre à jour le stock existant
            $oldQtt = $existingStock->getQtt();
            $oldQttRestant = $existingStock->getQttRestant();
            $newQtt = $instance->getQtt();
            $existingStock->setQtt($oldQtt + $newQtt);
            $existingStock->setQttRestant($oldQttRestant + $newQtt);
            $newStock = $existingStock; 
            $stockRestant = $stockProduit + $newQtt;

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
            $stockRestant = $stockProduit + $newStock->getQtt();
            
        }

        $this->entityManager->persist($newStock);

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

    public function edit($stock = null, $produitCategorie = null, $oldQtt = null, $datePeremption = null, $editQtt = null)
    {
        $stocks = $produitCategorie->getStocks();
        $datePeremptions = [];
        foreach($stocks as $stk) {
            if ($stk->getDatePeremption() !== null && $stk->getDatePeremption()->getDate() !== null) {
                $datePeremptions[] = $stk->getDatePeremption()->getDate()->format('d-m-Y');
            }
        }

        //verifier si $datePeremption existe déjà dans le tableau datePeremptions
        $formattedDatePeremption = $datePeremption ? $datePeremption->format('d-m-Y') : null;

        if ($formattedDatePeremption && in_array($formattedDatePeremption, $datePeremptions)) {
           // Convertir la date au format Y-m-d
            $formattedDateForSearch = $datePeremption->format('Y-m-d');

            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('s')
                ->from(Stock::class, 's')
                ->join('s.datePeremption', 'dp')
                ->join('s.produitCategorie', 'pc')
                ->where('dp.date LIKE :date')
                ->andWhere('pc.id = :produitCategorieId')
                ->setParameter('date', $formattedDateForSearch . '%') 
                ->setParameter('produitCategorieId', $produitCategorie->getId());

            $oldStock = $qb->getQuery()->getOneOrNullResult();

            //verifier si le oldStock existe et l'id de stock est different de l'id oldStock
            if ($oldStock && $oldStock->getId() != $stock->getId()) {
                $qttStock = $oldStock->getQtt();
                $oldStock->setQtt($qttStock + $editQtt);
                $oldStock->setQttRestant($oldStock->getQttRestant() + $editQtt);
                $this->entityManager->persist($oldStock);
                $this->entityManager->remove($stock);
            } else {
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
                /*if ($oldQtt <= $oldStockRestant) {
                    $stockRestant = $oldStockRestant - $oldQtt;
                    $newQtt = $stock->getQtt();
                    $stockRestant = $stockRestant + $newQtt;

                } else {
                    $stocktoAdd = $oldQtt - $oldStockRestant;
                    $stockRestant = $oldStockRestant + $stocktoAdd;

                }*/
                if ($oldQtt <= $oldStockRestant) {
                    $stockRestant = $oldStockRestant - $oldQtt;
                    $newQtt = $stock->getQtt();
                    $stockRestant = $stockRestant + $newQtt;
    
                } else {
                    $newQtt = $stock->getQtt(); 
                    if($newQtt > $oldQtt) {
                        $newStockRestant = $oldStockRestant + ($newQtt - $oldQtt);
                    } else {
                        $newStockRestant = $oldStockRestant - ($oldQtt - $newQtt);
                    }
                    $stockRestant = $newStockRestant;
                }
                
                $produitCategorie->setStockRestant($stockRestant);
                $this->entityManager->persist($produitCategorie);

                if ($newDatePeremption) {
                    $stock->setDatePeremption($newDatePeremption);
                }
                
                // Persist l'état actuel de stock
                $this->entityManager->persist($stock);
            }
        } else {
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

            } else {
                $newQtt = $stock->getQtt(); 
                if($newQtt > $oldQtt) {
                    $newStockRestant = $oldStockRestant + ($newQtt - $oldQtt);
                } else {
                    $newStockRestant = $oldStockRestant - ($oldQtt - $newQtt);
                }
                $stockRestant = $newStockRestant;
            }

            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
            //dd($produitCategorie->getStockRestant());

            if ($newDatePeremption) {
                $stock->setDatePeremption($newDatePeremption);
            } else {
                $stock->setDatePeremption(null);
            }           
            // Persist l'état actuel de stock
            
            $this->entityManager->persist($stock);
        }
        
        $this->update();

        // Obtenir l'utilisateur connecté
        $user = $this->security->getUser();

        // Créer log
        $this->logger->info('Stock de produit catégorie modifié', [
            'Produit' => $produitCategorie->getNom(),
            'Nom du responsable' => $user ? $user->getNom() : 'Utilisateur non connecté',
            'Adresse e-mail' => $user ? $user->getEmail() : 'Pas d\'adresse e-mail',
            'ID Application' => $produitCategorie->getApplication()->getId()
        ]);
        
        return $stock;
    }

     public function addTransfert($produitCategorie = null, $newApplication = null, $quantity = null, $stock = null, $newStock = null)
    {
        $transfert = new Transfert();
        $transfert->setProduitCategorie($produitCategorie);
        $transfert->setOldApplication($produitCategorie->getApplication());
        $transfert->setApplication($newApplication);
        $transfert->setQuantity($quantity);
        $transfert->setDateCreation(new \DateTime());
        $transfert->setStock($stock);
        $transfert->setNewStock($newStock);

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

    public function addNewProductForNewApplication($productReferenceExists = null, $oldProduitCategorie = null, $quantity = null, $application = null, $isChangePrice = null, $stock = null, $datePeremption = null, $oldApplication = null)
    {
        $this->updateStockRestant($oldProduitCategorie, $quantity, $oldApplication);

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

        $stocks = $newProduitCategorie->getStocks();
        $formattedDatePeremption = $datePeremption ? $datePeremption->format('d-m-Y') : '';

        $existingStock = null;

        // Trouver un stock existant avec la même date de péremption
        foreach ($stocks as $stk) {
            if($stk->getDatePeremption() && $stk->getDatePeremption()->getDate()) {
                $formattedDatePeremptionStock = $stk->getDatePeremption()->getDate()->format('d-m-Y');
                if ($formattedDatePeremption && $formattedDatePeremptionStock === $formattedDatePeremption) {
                    $existingStock = $stk;
                    break; 
                }
            }
        }

        $oldQttRestantStock = $stock->getQttRestant();
        $newStock = null;
        
        if($quantity < $oldQttRestantStock) {
            if($existingStock) {
                $newStock = $existingStock;
                $existingStock->setQtt($existingStock->getQtt() + $quantity);
                $existingStock->setQttRestant($existingStock->getQttRestant() + $quantity);
                $this->entityManager->persist($existingStock);
            } else {
                $newStock = new Stock();
                $newStock->setQtt($quantity);
                $newStock->setQttRestant($quantity);
                $newStock->setProduitCategorie($newProduitCategorie);
                  // Initialisation de la date de péremption
                  if ($datePeremption === null || $datePeremption === '') {
                    $newStock->setDatePeremption(null);
                } else {
                    $newDatePeremption = new DatePeremption();
                    $newDatePeremption->setDate($datePeremption);
                    $newDatePeremption->setDateCreation(new \DateTime());
    
                    $this->entityManager->persist($newDatePeremption);
                    $newStock->setDatePeremption($newDatePeremption);
                }
    
                $this->entityManager->persist($newStock);
            }

            $oldQttRestantStock -= $quantity;
            $stock->setQttRestant($oldQttRestantStock);
            $this->entityManager->persist($stock);
        } 

        $this->addTransfert($oldProduitCategorie, $application, $quantity, $stock, $newStock);

        if($newProduitCategorie->getStockRestant() != null) {
            $newProduitCategorie->setStockRestant($newProduitCategorie->getStockRestant() + $quantity);
        } else {
            $newProduitCategorie->setStockRestant($quantity);
        }

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

       // $this->update();

        //return [$newStock, $newProduitCategorie];

    }

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

}