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
        'Kg' => 'Kg',
        'G' => 'G',
        'sachet' => 'Sachet',
        'cp' => 'Comprimé'
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

    public function __construct()
    {
        $this->affaires = new ArrayCollection();
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
}
