<?php

namespace App\Entity;

use App\Repository\MethodePaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MethodePaiementRepository::class)]
class MethodePaiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?float $espece = null;

    #[ORM\Column(nullable: true)]
    private ?float $mVola = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceMvola = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomMvola = null;

    #[ORM\Column(nullable: true)]
    private ?float $airtelMoney = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceAirtel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomAirtel = null;

    #[ORM\Column(nullable: true)]
    private ?float $orangeMoney = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceOrange = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomOrange = null;

    #[ORM\ManyToOne(inversedBy: 'methodePaiements')]
    private ?Facture $facture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateMethodePaiement = null;

    #[ORM\ManyToOne(inversedBy: 'methodePaiements')]
    private ?Application $application = null;

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

    public function getEspece(): ?float
    {
        return $this->espece;
    }

    public function setEspece(?float $espece): static
    {
        $this->espece = $espece;

        return $this;
    }

    public function getMVola(): ?float
    {
        return $this->mVola;
    }

    public function setMVola(?float $mVola): static
    {
        $this->mVola = $mVola;

        return $this;
    }

    public function getReferenceMvola(): ?string
    {
        return $this->referenceMvola;
    }

    public function setReferenceMvola(?string $referenceMvola): static
    {
        $this->referenceMvola = $referenceMvola;

        return $this;
    }

    public function getNomMvola(): ?string
    {
        return $this->nomMvola;
    }

    public function setNomMvola(?string $nomMvola): static
    {
        $this->nomMvola = $nomMvola;

        return $this;
    }

    public function getAirtelMoney(): ?float
    {
        return $this->airtelMoney;
    }

    public function setAirtelMoney(?float $airtelMoney): static
    {
        $this->airtelMoney = $airtelMoney;

        return $this;
    }

    public function getReferenceAirtel(): ?string
    {
        return $this->referenceAirtel;
    }

    public function setReferenceAirtel(?string $referenceAirtel): static
    {
        $this->referenceAirtel = $referenceAirtel;

        return $this;
    }

    public function getNomAirtel(): ?string
    {
        return $this->nomAirtel;
    }

    public function setNomAirtel(?string $nomAirtel): static
    {
        $this->nomAirtel = $nomAirtel;

        return $this;
    }

    public function getOrangeMoney(): ?float
    {
        return $this->orangeMoney;
    }

    public function setOrangeMoney(?float $orangeMoney): static
    {
        $this->orangeMoney = $orangeMoney;

        return $this;
    }

    public function getReferenceOrange(): ?string
    {
        return $this->referenceOrange;
    }

    public function setReferenceOrange(?string $referenceOrange): static
    {
        $this->referenceOrange = $referenceOrange;

        return $this;
    }

    public function getNomOrange(): ?string
    {
        return $this->nomOrange;
    }

    public function setNomOrange(?string $nomOrange): static
    {
        $this->nomOrange = $nomOrange;

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

    public function getDateMethodePaiement(): ?\DateTimeInterface
    {
        return $this->dateMethodePaiement;
    }

    public function setDateMethodePaiement(?\DateTimeInterface $dateMethodePaiement): static
    {
        $this->dateMethodePaiement = $dateMethodePaiement;

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
}
