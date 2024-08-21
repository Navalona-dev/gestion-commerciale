<?php

namespace App\Entity;

use App\Repository\FactureEcheanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureEcheanceRepository::class)]
class FactureEcheance
{
    const STATUS = [
        'regle' => 'Réglée',
        'presenter' => 'Présenter',
        'reglePartiel' => 'Rglt. partiel',
        'annule' => 'Annulée',
        'encours' => 'En cours'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $delaiPaiement = null;

    #[ORM\ManyToOne(inversedBy: 'factureEcheances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facture $facture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEcheance = null;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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

    public function getDelaiPaiement(): ?string
    {
        return $this->delaiPaiement;
    }

    public function setDelaiPaiement(?string $delaiPaiement): static
    {
        $this->delaiPaiement = $delaiPaiement;

        return $this;
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

    public function getDateEcheance(): ?\DateTimeInterface
    {
        return $this->dateEcheance;
    }

    public function setDateEcheance(?\DateTimeInterface $dateEcheance): static
    {
        $this->dateEcheance = $dateEcheance;

        return $this;
    }
}
