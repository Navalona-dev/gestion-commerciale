<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FactureBeneficeRepository;

#[ORM\Entity(repositoryClass: FactureBeneficeRepository::class)]
class FactureBenefice
{
    const MODE_RGLT = [
        //'Non precisé' => '',
        //'par LCR' => 'lcr',
        //'par prélèvement' => 'prl',
        //'par chèque' => 'chq',
        //'par virement' => 'vir'
        'espece' => 'espece'
    ];

    const STATUT = [
        'regle' => 'Réglée',
        'presenter' => 'Présenter',
        'reglePartiel' => 'Rglt. partiel',
        'annule' => 'Annulée',
        'encours' => 'En cours',
        'termine' => 'Terminée'
    ];

    const ETAT = [
        'a_regler' => 'A régler',
        'regle' => 'Réglée',
        'annule' => 'Annulée',
        'encours' => 'En cours',
        'enecheance' => 'En echeance',
        'termine' => 'Terminée'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?int $numero = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixHt = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixTtc = null;

    #[ORM\Column(nullable: true)]
    private ?float $solde = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(nullable: true)]
    private ?float $reglement = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?float $remise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'factureBenefices')]
    private ?Application $application = null;

    #[ORM\ManyToOne(inversedBy: 'factureBenefices')]
    private ?Benefice $benefice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

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

    public function getPrixTtc(): ?float
    {
        return $this->prixTtc;
    }

    public function setPrixTtc(?float $prixTtc): static
    {
        $this->prixTtc = $prixTtc;

        return $this;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(?float $solde): static
    {
        $this->solde = $solde;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getReglement(): ?float
    {
        return $this->reglement;
    }

    public function setReglement(?float $reglement): static
    {
        $this->reglement = $reglement;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->isValid;
    }

    public function setValid(?bool $isValid): static
    {
        $this->isValid = $isValid;

        return $this;
    }


    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): static
    {
        $this->file = $file;

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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

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

    public function getBenefice(): ?Benefice
    {
        return $this->benefice;
    }

    public function setBenefice(?Benefice $benefice): static
    {
        $this->benefice = $benefice;

        return $this;
    }

   
   

}
