<?php

namespace App\Entity;

use App\Exception\PropertyVideException;
use App\Repository\ProduitCategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitCategorieRepository::class)]
class ProduitCategorie
{
    const uniteVenteGros = [
        'sac' => 'Sac',
        'flacon' => 'Flacon',
        'granule' => 'Granule',
        'sht' => 'SHT',
        'pippette' => 'Pippette',
        'spray' => 'Spray',
        'bloc' => 'Bloc',
        'boite' => 'Boîte'
    ];

    const uniteVenteDetails = [
        'unite' => 'Unité',
        'l' => 'L',
        'ml' => 'ML',
        'cc' => 'CC',
        'sachet' => 'Sachet'
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

    #[ORM\ManyToOne(inversedBy: 'produitCategories')]
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
    private ?float $presentationDetail = null;

    #[ORM\Column(nullable: true)]
    private ?float $presentationGros = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->productImages = new ArrayCollection();
        $this->stocks = new ArrayCollection();
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

    public function getPresentationDetail(): ?float
    {
        return $this->presentationDetail;
    }

    public function setPresentationDetail(?float $presentationDetail): static
    {
        $this->presentationDetail = $presentationDetail;

        return $this;
    }

    public function getPresentationGros(): ?float
    {
        return $this->presentationGros;
    }

    public function setPresentationGros(?float $presentationGros): static
    {
        $this->presentationGros = $presentationGros;

        return $this;
    }
}
