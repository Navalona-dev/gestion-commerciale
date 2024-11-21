<?php

namespace App\Entity;

use App\Repository\ComptabiliteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComptabiliteRepository::class)]
class Comptabilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $designation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    /**
     * @var Collection<int, Depense>
     */
    #[ORM\ManyToMany(targetEntity: Depense::class, inversedBy: 'comptabilites')]
    private Collection $depenses;

    #[ORM\Column(nullable: true)]
    private ?float $reste = null;

    /**
     * @var Collection<int, FactureComptabilite>
     */
    #[ORM\OneToMany(targetEntity: FactureComptabilite::class, mappedBy: 'comptabilite')]
    private Collection $factureComptabilites;

    #[ORM\ManyToOne(inversedBy: 'comptabilites')]
    private ?Fourchette $fourchette = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateComptabilite = null;

    #[ORM\ManyToOne(inversedBy: 'comptabilites')]
    private ?Application $application = null;

    /**
     * @var Collection<int, Benefice>
     */
    #[ORM\ManyToMany(targetEntity: Benefice::class, inversedBy: 'comptabilites')]
    private Collection $benefices;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
        $this->factureComptabilites = new ArrayCollection();
        $this->benefices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection<int, Depense>
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): static
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses->add($depense);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): static
    {
        $this->depenses->removeElement($depense);

        return $this;
    }

    public function getReste(): ?float
    {
        return $this->reste;
    }

    public function setReste(?float $reste): static
    {
        $this->reste = $reste;

        return $this;
    }

    /**
     * @return Collection<int, FactureComptabilite>
     */
    public function getFactureComptabilites(): Collection
    {
        return $this->factureComptabilites;
    }

    public function addFactureComptabilite(FactureComptabilite $factureComptabilite): static
    {
        if (!$this->factureComptabilites->contains($factureComptabilite)) {
            $this->factureComptabilites->add($factureComptabilite);
            $factureComptabilite->setComptabilite($this);
        }

        return $this;
    }

    public function removeFactureComptabilite(FactureComptabilite $factureComptabilite): static
    {
        if ($this->factureComptabilites->removeElement($factureComptabilite)) {
            // set the owning side to null (unless already changed)
            if ($factureComptabilite->getComptabilite() === $this) {
                $factureComptabilite->setComptabilite(null);
            }
        }

        return $this;
    }

    public function getFourchette(): ?Fourchette
    {
        return $this->fourchette;
    }

    public function setFourchette(?Fourchette $fourchette): static
    {
        $this->fourchette = $fourchette;

        return $this;
    }

    public function getDateComptabilite(): ?\DateTimeInterface
    {
        return $this->dateComptabilite;
    }

    public function setDateComptabilite(?\DateTimeInterface $dateComptabilite): static
    {
        $this->dateComptabilite = $dateComptabilite;

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
     * @return Collection<int, Benefice>
     */
    public function getBenefices(): Collection
    {
        return $this->benefices;
    }

    public function addBenefice(Benefice $benefice): static
    {
        if (!$this->benefices->contains($benefice)) {
            $this->benefices->add($benefice);
        }

        return $this;
    }

    public function removeBenefice(Benefice $benefice): static
    {
        $this->benefices->removeElement($benefice);

        return $this;
    }
}
