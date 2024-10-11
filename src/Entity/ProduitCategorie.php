<?php

namespace App\Entity;

use App\Exception\PropertyVideException;
use App\Repository\ProduitCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ProduitCategorieRepository::class)]
class ProduitCategorie
{
    const presentationVente = [
        'sac' => 'Sac',
        'flacon' => 'Flacon',
        'granule' => 'Granule',
        'pcs' => 'PCS',
        'pippette' => 'Pippette',
        'spray' => 'Spray',
        'bloc' => 'Bloc',
        'boite' => 'Boîte',
        'sachet' => 'Sachet',
        'cp' => 'CP'
    ];

    const uniteVenteGros = [
        'unite' => 'Unité',
        'l' => 'L',
        'kg' => 'Kg',
        'ml' => 'Ml',
        'pcs' => 'PCS',
        'sachet' => 'Sachet',
        'cp' => 'CP',
        'pipette' => 'Pipette',
        'bloc' => 'Bloc',
        'boite' => 'Boîte',
        'flacon' => 'Flacon'
    ];

    const uniteVenteDetails = [
        'unite' => 'Unité',
        'l' => 'L',
        'kg' => 'Kg',
        'ml' => 'Ml',
        'pcs' => 'PCS',
        'sachet' => 'Sachet',
        'cp' => 'CP',
        'pipette' => 'Pipette',
        'bloc' => 'Bloc',
        'boite' => 'Boîte',
        'flacon' => 'Flacon'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixHt = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\Column(nullable: true)]
    private ?float $qtt = null;

    #[ORM\Column(nullable: true)]
    private ?float $stockRestant = null;

    #[ORM\Column(nullable: true)]
    private ?float $stockMin = null;

    #[ORM\Column(nullable: true)]
    private ?float $stockMax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uniteVenteGros = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uniteVenteDetail = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixVenteGros = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixVenteDetail = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixTTC = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixAchat = null;

    #[ORM\ManyToOne(inversedBy: 'produitCategories', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Application $application = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'produitCategorie')]
    private Collection $produits;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    /**
     * @var Collection<int, Stock>
     */
    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'produitCategorie', cascade:["remove"])]
    private Collection $stocks;

    #[ORM\ManyToOne(inversedBy: 'produitCategories')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'produitCategories')]
    private ?ProduitType $type = null;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(targetEntity: ProductImage::class, mappedBy: 'produitCategorie')]
    private Collection $productImages;

    #[ORM\Column(nullable: true)]
    private ?string $presentationDetail = null;

    #[ORM\Column(nullable: true)]
    private ?string $presentationGros = null;

    /**
     * @var Collection<int, Compte>
     */
    #[ORM\ManyToMany(targetEntity: Compte::class, inversedBy: 'produitCategories')]
    private Collection $comptes;

    #[ORM\Column(nullable: true)]
    private ?float $volumeGros = null;

    #[ORM\Column(nullable: true)]
    private ?float $volumeDetail = null;

    /**
     * @var Collection<int, Transfert>
     */
    #[ORM\OneToMany(targetEntity: Transfert::class, mappedBy: 'produitCategorie')]
    private Collection $transferts;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'produitCategorie')]
    private Collection $notifications;

    #[ORM\Column(nullable: true)]
    private ?bool $isChangePrix = null;

    #[ORM\Column(nullable: true)]
    private ?float $qttReserver = null;

    #[ORM\Column(nullable: true)]
    private ?float $qttReserverCommander = null;

    #[ORM\Column(nullable: true)]
    private ?float $qttReserverDetail = null;

    #[ORM\Column(nullable: true)]
    private ?float $qttReserverGros = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->productImages = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->comptes = new ArrayCollection();
        $this->transferts = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public static function newProduitCategorie($instance = null)
    {
        if (is_null($instance->getNom()) or empty($instance->getNom())) {
            throw new PropertyVideException("Your name doesn't empty");
        }

        return $instance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPrixHt(): ?float
    {
        return $this->prixHt;
    }

    public function setPrixHt(?float $prixHt): static
    {
        $this->prixHt = $prixHt;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): static
    {
        $this->tva = $tva;

        return $this;
    }

    public function getQtt(): ?float
    {
        return $this->qtt;
    }

    public function setQtt(?float $qtt): static
    {
        $this->qtt = $qtt;

        return $this;
    }

    public function getStockRestant(): ?float
    {
        return $this->stockRestant;
    }

    public function setStockRestant(?float $stockRestant): static
    {
        $this->stockRestant = $stockRestant;

        return $this;
    }

    public function getStockMin(): ?float
    {
        return $this->stockMin;
    }

    public function setStockMin(?float $stockMin): static
    {
        $this->stockMin = $stockMin;

        return $this;
    }

    public function getStockMax(): ?float
    {
        return $this->stockMax;
    }

    public function setStockMax(?float $stockMax): static
    {
        $this->stockMax = $stockMax;

        return $this;
    }

    public function getUniteVenteGros(): ?string
    {
        return $this->uniteVenteGros;
    }

    public function setUniteVenteGros(?string $uniteVenteGros): static
    {
        $this->uniteVenteGros = $uniteVenteGros;

        return $this;
    }

    public function getUniteVenteDetail(): ?string
    {
        return $this->uniteVenteDetail;
    }

    public function setUniteVenteDetail(?string $uniteVenteDetail): static
    {
        $this->uniteVenteDetail = $uniteVenteDetail;

        return $this;
    }

    public function getPrixVenteGros(): ?float
    {
        return $this->prixVenteGros;
    }

    public function setPrixVenteGros(?float $prixVenteGros): static
    {
        $this->prixVenteGros = $prixVenteGros;

        return $this;
    }

    public function getPrixVenteDetail(): ?float
    {
        return $this->prixVenteDetail;
    }

    public function setPrixVenteDetail(?float $prixVenteDetail): static
    {
        $this->prixVenteDetail = $prixVenteDetail;

        return $this;
    }

    public function getPrixTTC(): ?float
    {
        return $this->prixTTC;
    }

    public function setPrixTTC(?float $prixTTC): static
    {
        $this->prixTTC = $prixTTC;

        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Product $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setProduitCategorie($this);
        }

        return $this;
    }

    public function removeProduit(Product $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getProduitCategorie() === $this) {
                $produit->setProduitCategorie(null);
            }
        }

        return $this;
    }


    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setProduitCategorie($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getProduitCategorie() === $this) {
                $stock->setProduitCategorie(null);
            }
        }

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getType(): ?ProduitType
    {
        return $this->type;
    }

    public function setType(?ProduitType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduitCategorie($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            // set the owning side to null (unless already changed)
            if ($productImage->getProduitCategorie() === $this) {
                $productImage->setProduitCategorie(null);
            }
        }

        return $this;
    }

    public function getPresentationDetail(): ?string
    {
        return $this->presentationDetail;
    }

    public function setPresentationDetail(?string $presentationDetail): static
    {
        $this->presentationDetail = $presentationDetail;

        return $this;
    }

    public function getPresentationGros(): ?string
    {
        return $this->presentationGros;
    }

    public function setPresentationGros(?string $presentationGros): static
    {
        $this->presentationGros = $presentationGros;

        return $this;
    }

    /**
     * @return Collection<int, Compte>
     */
    public function getComptes(): Collection
    {
        return $this->comptes;
    }

    public function addCompte(Compte $compte): static
    {
        if (!$this->comptes->contains($compte)) {
            $this->comptes->add($compte);
        }

        return $this;
    }

    public function removeCompte(Compte $compte): static
    {
        $this->comptes->removeElement($compte);

        return $this;
    }

    public function getVolumeGros(): ?float
    {
        return $this->volumeGros;
    }

    public function setVolumeGros(?float $volumeGros): static
    {
        $this->volumeGros = $volumeGros;

        return $this;
    }

    public function getVolumeDetail(): ?float
    {
        return $this->volumeDetail;
    }

    public function setVolumeDetail(?float $volumeDetail): static
    {
        $this->volumeDetail = $volumeDetail;

        return $this;
    }

    /**
     * @return Collection<int, Transfert>
     */
    public function getTransferts(): Collection
    {
        return $this->transferts;
    }

    public function addTransfert(Transfert $transfert): static
    {
        if (!$this->transferts->contains($transfert)) {
            $this->transferts->add($transfert);
            $transfert->setProduitCategorie($this);
        }

        return $this;
    }

    public function removeTransfert(Transfert $transfert): static
    {
        if ($this->transferts->removeElement($transfert)) {
            // set the owning side to null (unless already changed)
            if ($transfert->getProduitCategorie() === $this) {
                $transfert->setProduitCategorie(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setProduitCategorie($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getProduitCategorie() === $this) {
                $notification->setProduitCategorie(null);
            }
        }

        return $this;
    }

    public function getIsChangePrix(): ?bool
    {
        return $this->isChangePrix;
    }

    public function setIsChangePrix(?bool $isChangePrix): static
    {
        $this->isChangePrix = $isChangePrix;

        return $this;
    }

    public function getQttReserver(): ?float
    {
        return $this->qttReserver;
    }

    public function setQttReserver(?float $qttReserver): static
    {
        $this->qttReserver = $qttReserver;

        return $this;
    }

    public function getQttReserverCommander(): ?float
    {
        return $this->qttReserverCommander;
    }

    public function setQttReserverCommander(?float $qttReserverCommander): static
    {
        $this->qttReserverCommander = $qttReserverCommander;

        return $this;
    }

    public function getQttReserverDetail(): ?float
    {
        return $this->qttReserverDetail;
    }

    public function setQttReserverDetail(?float $qttReserverDetail): static
    {
        $this->qttReserverDetail = $qttReserverDetail;

        return $this;
    }

    public function getQttReserverGros(): ?float
    {
        return $this->qttReserverGros;
    }

    public function setQttReserverGros(?float $qttReserverGros): static
    {
        $this->qttReserverGros = $qttReserverGros;

        return $this;
    }
}
