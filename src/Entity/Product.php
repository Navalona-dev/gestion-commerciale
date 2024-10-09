<?php

namespace App\Entity;

use App\Exception\PropertyVideException;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
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
        'sachet' => 'Sachet'
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
    
    const TYPE_REDUCTION = [
        'remise' => 'Remise commerciale'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?float $puHt = null;

    #[ORM\Column(nullable: true)]
    private ?float $puTTC = null;

    #[ORM\Column(nullable: true)]
    private ?float $tva = null;

    #[ORM\Column(nullable: true)]
    private ?float $qtt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?float $remise = null;

    #[ORM\Column(nullable: true)]
    private ?int $remisePourcent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unite = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Application $application = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(name: "produitCategorie_id", referencedColumnName: "id", onDelete: 'SET NULL')]
    private ?ProduitCategorie $produitCategorie = null;

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

    /**
     * @var Collection<int, Affaire>
     */
    #[ORM\ManyToMany(targetEntity: Affaire::class, inversedBy: 'products')]
    private Collection $affaires;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeVente = null;

    /**
     * @var Collection<int, FactureDetail>
     */
    #[ORM\OneToMany(targetEntity: FactureDetail::class, mappedBy: 'product')]
    private Collection $factureDetails;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePeremption = null;

    /**
     * @var Collection<int, DatePeremptionProduct>
     */
    #[ORM\OneToMany(targetEntity: DatePeremptionProduct::class, mappedBy: 'product')]
    private Collection $datePeremptionProducts;

    #[ORM\Column(nullable: true)]
    private ?float $qttRestant = null;

    #[ORM\Column(nullable: true)]
    private ?float $qttVendu = null;

    #[ORM\Column(nullable: true)]
    private ?float $dejaPaye = null;

    #[ORM\Column(nullable: true)]
    private ?float $restePayer = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeReduction = null;

    public function __construct()
    {
        $this->affaires = new ArrayCollection();
        $this->factureDetails = new ArrayCollection();
        $this->datePeremptionProducts = new ArrayCollection();
    }

    public static function newProduct($instance = null)
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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

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

    public function getPuHt(): ?float
    {
        return $this->puHt;
    }

    public function setPuHt(?float $puHt): static
    {
        $this->puHt = $puHt;

        return $this;
    }

    public function getPuTTC(): ?float
    {
        return $this->puTTC;
    }

    public function setPuTTC(?float $puTTC): static
    {
        $this->puTTC = $puTTC;

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(?float $remise): static
    {
        $this->remise = $remise;

        return $this;
    }

    public function getRemisePourcent(): ?int
    {
        return $this->remisePourcent;
    }

    public function setRemisePourcent(?int $remisePourcent): static
    {
        $this->remisePourcent = $remisePourcent;

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(?string $unite): static
    {
        $this->unite = $unite;

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

    public function getProduitCategorie(): ?ProduitCategorie
    {
        return $this->produitCategorie;
    }

    public function setProduitCategorie(?ProduitCategorie $produitCategorie): static
    {
        $this->produitCategorie = $produitCategorie;

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

    /**
     * @return Collection<int, Affaire>
     */
    public function getAffaires(): Collection
    {
        return $this->affaires;
    }

    public function addAffaire(Affaire $affaire): static
    {
        if (!$this->affaires->contains($affaire)) {
            $this->affaires->add($affaire);
        }

        return $this;
    }

    public function removeAffaire(Affaire $affaire): static
    {
        $this->affaires->removeElement($affaire);

        return $this;
    }

    public function getTypeVente(): ?string
    {
        return $this->typeVente;
    }

    public function setTypeVente(?string $typeVente): static
    {
        $this->typeVente = $typeVente;

        return $this;
    }

    /**
     * @return Collection<int, FactureDetail>
     */
    public function getFactureDetails(): Collection
    {
        return $this->factureDetails;
    }

    public function addFactureDetail(FactureDetail $factureDetail): static
    {
        if (!$this->factureDetails->contains($factureDetail)) {
            $this->factureDetails->add($factureDetail);
            $factureDetail->setProduct($this);
        }

        return $this;
    }

    public function removeFactureDetail(FactureDetail $factureDetail): static
    {
        if ($this->factureDetails->removeElement($factureDetail)) {
            // set the owning side to null (unless already changed)
            if ($factureDetail->getProduct() === $this) {
                $factureDetail->setProduct(null);
            }
        }

        return $this;
    }

    public function getDatePeremption(): ?\DateTimeInterface
    {
        return $this->datePeremption;
    }

    public function setDatePeremption(?\DateTimeInterface $datePeremption): static
    {
        $this->datePeremption = $datePeremption;

        return $this;
    }

    /**
     * @return Collection<int, DatePeremptionProduct>
     */
    public function getDatePeremptionProducts(): Collection
    {
        return $this->datePeremptionProducts;
    }

    public function addDatePeremptionProduct(DatePeremptionProduct $datePeremptionProduct): static
    {
        if (!$this->datePeremptionProducts->contains($datePeremptionProduct)) {
            $this->datePeremptionProducts->add($datePeremptionProduct);
            $datePeremptionProduct->setProduct($this);
        }

        return $this;
    }

    public function removeDatePeremptionProduct(DatePeremptionProduct $datePeremptionProduct): static
    {
        if ($this->datePeremptionProducts->removeElement($datePeremptionProduct)) {
            // set the owning side to null (unless already changed)
            if ($datePeremptionProduct->getProduct() === $this) {
                $datePeremptionProduct->setProduct(null);
            }
        }

        return $this;
    }
        
    public function getTypeReduction(): ?string
    {
        return $this->typeReduction;
    }

    public function setTypeReduction(?string $typeReduction): static
    {
        $this->typeReduction = $typeReduction;

        return $this;
    }

    public function getQttRestant(): ?float
    {
        return $this->qttRestant;
    }

    public function setQttRestant(?float $qttRestant): static
    {
        $this->qttRestant = $qttRestant;

        return $this;
    }

    public function getQttVendu(): ?float
    {
        return $this->qttVendu;
    }

    public function setQttVendu(?float $qttVendu): static
    {
        $this->qttVendu = $qttVendu;

        return $this;
    }

    public function getDejaPaye(): ?float
    {
        return $this->dejaPaye;
    }

    public function setDejaPaye(?float $dejaPaye): static
    {
        $this->dejaPaye = $dejaPaye;

        return $this;
    }

    public function getRestePayer(): ?float
    {
        return $this->restePayer;
    }

    public function setRestePayer(?float $restePayer): static
    {
        $this->restePayer = $restePayer;

        return $this;
    }
}
