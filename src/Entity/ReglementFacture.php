<?php

namespace App\Entity;

use App\Repository\ReglementFactureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReglementFactureRepository::class)]
class ReglementFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reglementFactures')]
    private ?Facture $facture = null;

    #[ORM\Column(nullable: true)]
    private ?int $numero = null;

    #[ORM\Column(nullable: true)]
    private ?int $numeroFacture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReglement = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isToutPaye = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        $this->facture = $facture;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(?int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getNumeroFacture(): ?int
    {
        return $this->numeroFacture;
    }

    public function setNumeroFacture(?int $numeroFacture): static
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    public function getDateReglement(): ?\DateTimeInterface
    {
        return $this->dateReglement;
    }

    public function setDateReglement(?\DateTimeInterface $dateReglement): static
    {
        $this->dateReglement = $dateReglement;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function isToutPaye(): ?bool
    {
        return $this->isToutPaye;
    }

    public function setToutPaye(?bool $isToutPaye): static
    {
        $this->isToutPaye = $isToutPaye;

        return $this;
    }
}
