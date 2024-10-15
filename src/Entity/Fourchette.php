<?php

namespace App\Entity;

use App\Repository\FourchetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FourchetteRepository::class)]
class Fourchette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?float $minVal = null;

    #[ORM\Column(nullable: true)]
    private ?float $maxVal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    /**
     * @var Collection<int, Comptabilite>
     */
    #[ORM\OneToMany(targetEntity: Comptabilite::class, mappedBy: 'fourchette')]
    private Collection $comptabilites;

    public function __construct()
    {
        $this->comptabilites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinVal(): ?float
    {
        return $this->minVal;
    }

    public function setMinVal(?float $minVal): static
    {
        $this->minVal = $minVal;

        return $this;
    }

    public function getMaxVal(): ?float
    {
        return $this->maxVal;
    }

    public function setMaxVal(?float $maxVal): static
    {
        $this->maxVal = $maxVal;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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
            $comptabilite->setFourchette($this);
        }

        return $this;
    }

    public function removeComptabilite(Comptabilite $comptabilite): static
    {
        if ($this->comptabilites->removeElement($comptabilite)) {
            // set the owning side to null (unless already changed)
            if ($comptabilite->getFourchette() === $this) {
                $comptabilite->setFourchette(null);
            }
        }

        return $this;
    }
}
