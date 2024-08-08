<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?ProduitCategorie $produitCategorie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    private ?Application $application = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isView = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isStockMin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isStockMax = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

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

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): static
    {
        $this->application = $application;

        return $this;
    }

    public function getIsView(): ?bool
    {
        return $this->isView;
    }

    public function setIsView(?bool $isView): static
    {
        $this->isView = $isView;

        return $this;
    }

    public function isStockMin(): ?bool
    {
        return $this->isStockMin;
    }

    public function setStockMin(?bool $isStockMin): static
    {
        $this->isStockMin = $isStockMin;

        return $this;
    }

    public function isStockMax(): ?bool
    {
        return $this->isStockMax;
    }

    public function setStockMax(?bool $isStockMax): static
    {
        $this->isStockMax = $isStockMax;

        return $this;
    }
}
