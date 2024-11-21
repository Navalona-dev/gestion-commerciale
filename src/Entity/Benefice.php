<?php

namespace App\Entity;

use App\Repository\BeneficeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BeneficeRepository::class)]
class Benefice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation = null;

    /**
     * @var Collection<int, Facture>
     */
    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: 'benefice')]
    private Collection $factures;

    #[ORM\Column(nullable: true)]
    private ?float $espece = null;

    #[ORM\Column(nullable: true)]
    private ?float $mobileMoney = null;

    #[ORM\Column(nullable: true)]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'benefices')]
    private ?Application $application = null;

    /**
     * @var Collection<int, FactureBenefice>
     */
    #[ORM\OneToMany(targetEntity: FactureBenefice::class, mappedBy: 'benefice')]
    private Collection $factureBenefices;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateBenefice = null;

    /**
     * @var Collection<int, Comptabilite>
     */
    #[ORM\ManyToMany(targetEntity: Comptabilite::class, mappedBy: 'benefices')]
    private Collection $comptabilites;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
        $this->factureBenefices = new ArrayCollection();
        $this->comptabilites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): static
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setBenefice($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getBenefice() === $this) {
                $facture->setBenefice(null);
            }
        }

        return $this;
    }

    public function getEspece(): ?float
    {
        return $this->espece;
    }

    public function setEspece(?float $espece): static
    {
        $this->espece = $espece;

        return $this;
    }

    public function getMobileMoney(): ?float
    {
        return $this->mobileMoney;
    }

    public function setMobileMoney(?float $mobileMoney): static
    {
        $this->mobileMoney = $mobileMoney;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): static
    {
        $this->total = $total;

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
     * @return Collection<int, FactureBenefice>
     */
    public function getFactureBenefices(): Collection
    {
        return $this->factureBenefices;
    }

    public function addFactureBenefice(FactureBenefice $factureBenefice): static
    {
        if (!$this->factureBenefices->contains($factureBenefice)) {
            $this->factureBenefices->add($factureBenefice);
            $factureBenefice->setBenefice($this);
        }

        return $this;
    }

    public function removeFactureBenefice(FactureBenefice $factureBenefice): static
    {
        if ($this->factureBenefices->removeElement($factureBenefice)) {
            // set the owning side to null (unless already changed)
            if ($factureBenefice->getBenefice() === $this) {
                $factureBenefice->setBenefice(null);
            }
        }

        return $this;
    }

    public function getDateBenefice(): ?\DateTimeInterface
    {
        return $this->dateBenefice;
    }

    public function setDateBenefice(?\DateTimeInterface $dateBenefice): static
    {
        $this->dateBenefice = $dateBenefice;

        return $this;
    }

    /**
     * @return Collection<int, Comptabilite>
     */
    public function getComptabilites(): Collection
    {
        return $this->comptabilites;
    }

    public function addComptabilite(Comptabilite $comptabilite): static
    {
        if (!$this->comptabilites->contains($comptabilite)) {
            $this->comptabilites->add($comptabilite);
            $comptabilite->addBenefice($this);
        }

        return $this;
    }

    public function removeComptabilite(Comptabilite $comptabilite): static
    {
        if ($this->comptabilites->removeElement($comptabilite)) {
            $comptabilite->removeBenefice($this);
        }

        return $this;
    }
}
