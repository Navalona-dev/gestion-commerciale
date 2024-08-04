<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\StockRepository;
use App\Exception\PropertyVideException;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private ?float $qtt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    private ?ProduitCategorie $produitCategorie = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    private ?DatePeremption $datePeremption = null;

    public static function newStock($instance = null)
    {
        if (is_null($instance->getQtt()) or empty($instance->getQtt())) {
            throw new PropertyVideException("Your quantity doesn't empty");
        }

        return $instance;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduitCategorie(): ?ProduitCategorie
    {
        return $this->produitCategorie;
    }

    public function setProduitCategorie(?ProduitCategorie $produitCategorie): static
    {
        $this->produitCategorie = $produitCategorie;

        return $this;
    }

    public function getDatePeremption(): ?DatePeremption
    {
        return $this->datePeremption;
    }

    public function setDatePeremption(?DatePeremption $datePeremption): static
    {
        $this->datePeremption = $datePeremption;

        return $this;
    }
}
