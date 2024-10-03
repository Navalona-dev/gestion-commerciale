<?php

namespace App\Entity;

use App\Repository\TransfertRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransfertRepository::class)]
class Transfert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'transferts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Application $application = null;

    #[ORM\ManyToOne(inversedBy: 'transferts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Application $oldApplication = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'transferts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProduitCategorie $produitCategorie = null;

    #[ORM\ManyToOne(inversedBy: 'transferts')]
    private ?Stock $stock = null;

    #[ORM\ManyToOne]
    private ?Stock $newStock = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

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

    public function getOldApplication(): ?Application
    {
        return $this->oldApplication;
    }

    public function setOldApplication(?Application $oldApplication): static
    {
        $this->oldApplication = $oldApplication;

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

    public function getProduitCategorie(): ?ProduitCategorie
    {
        return $this->produitCategorie;
    }

    public function setProduitCategorie(?ProduitCategorie $produitCategorie): static
    {
        $this->produitCategorie = $produitCategorie;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getNewStock(): ?Stock
    {
        return $this->newStock;
    }

    public function setNewStock(?Stock $newStock): static
    {
        $this->newStock = $newStock;

        return $this;
    }

}
