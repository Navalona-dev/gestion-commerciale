<?php
namespace App\Service;

use App\Entity\Stock;
use App\Entity\Categorie;
use App\Entity\Notification;
use App\Entity\FactureDetail;
use Doctrine\ORM\EntityManager;
use App\Service\ApplicationManager;
use App\Service\AuthorizationManager;
use App\Exception\PropertyVideException;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\ActionInvalideException;
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

    public function __construct(ApplicationManager  $applicationManager, AuthorizationManager $authorization, TokenStorageInterface  $TokenStorageInterface, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();

    }

    public function add($instance, $produitCategorie)
    {
        $stock = Stock::newStock($instance);

        $date = new \DateTime();

        $stock->setDateCreation($date);
        $stock->setProduitCategorie($produitCategorie);

        $this->entityManager->persist($stock);

        $stockProduit = ($produitCategorie->getStockRestant() === null) ? 0 : $produitCategorie->getStockRestant();

        $stockRestant = $stockProduit + $stock->getQtt();
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

        $this->update();
        unset($instance);
        return $stock;
    }

    public function edit($stock, $produitCategorie, $oldQtt)
    {
    
        // Obtenez la quantité et le stock restant actuels
        //$oldQtt = $stock->getQtt();
        $oldStockRestant = $produitCategorie->getStockRestant();
    
        // Calculez le nouveau stock restant après soustraction de l'ancienne quantité
        //d($oldStockRestant, $oldQtt);
        if ($oldQtt <= $oldStockRestant) {
            $stockRestant = $oldStockRestant - $oldQtt;
            $newQtt = $stock->getQtt();
            $stockRestant = $stockRestant + $newQtt;
           
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
            
            // Persist l'état actuel de stock
            $this->entityManager->persist($stock);
            $this->update();
            
            return $stock;
        } else {
            $stocktoAdd = $oldQtt - $oldStockRestant;
            $stockRestant = $oldStockRestant + $stocktoAdd;
           
            $produitCategorie->setStockRestant($stockRestant);
            $this->entityManager->persist($produitCategorie);
            
            // Persist l'état actuel de stock
            $this->entityManager->persist($stock);
            //dd($stockRestant, $stocktoAdd, $oldStockRestant, $produitCategorie->getStockRestant(), $stock);
            $this->update();
        }
        return $stock;
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