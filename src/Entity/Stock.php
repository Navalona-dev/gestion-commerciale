<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?float $qttRestant = null;

    /**
     * @var Collection<int, DatePeremptionProduct>
     */
    #[ORM\OneToMany(targetEntity: DatePeremptionProduct::class, mappedBy: 'stock')]
    private Collection $datePeremptionProducts;

    /**
     * @var Collection<int, Transfert>
     */
    #[ORM\OneToMany(targetEntity: Transfert::class, mappedBy: 'stock')]
    private Collection $transferts;

    public function __construct()
    {
        $this->datePeremptionProducts = new ArrayCollection();
        $this->transferts = new ArrayCollection();
    }

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

    public function getQttRestant(): ?float
    {
        return $this->qttRestant;
    }

    public function setQttRestant(?float $qttRestant): static
    {
        $this->qttRestant = $qttRestant;

        return $this;
    }

    /**
     * @return Collection<int, DatePeremptionProduct>
     */
    public function getDatePeremptionProducts(): Collection
    {
        return $this->datePeremptionProducts;
    }

    public function addDatePeremptionProduct(DatePeremptionProduct $datePeremptionProduct): static
    {
        if (!$this->datePeremptionProducts->contains($datePeremptionProduct)) {
            $this->datePeremptionProducts->add($datePeremptionProduct);
            $datePeremptionProduct->setStock($this);
        }

        return $this;
    }

    public function removeDatePeremptionProduct(DatePeremptionProduct $datePeremptionProduct): static
    {
        if ($this->datePeremptionProducts->removeElement($datePeremptionProduct)) {
            // set the owning side to null (unless already changed)
            if ($datePeremptionProduct->getStock() === $this) {
                $datePeremptionProduct->setStock(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transfert>
     */
    public function getTransferts(): Collection
    {
        return $this->transferts;
    }

    public function addTransfert(Transfert $transfert): static
    {
        if (!$this->transferts->contains($transfert)) {
            $this->transferts->add($transfert);
            $transfert->setStock($this);
        }

        return $this;
    }

    public function removeTransfert(Transfert $transfert): static
    {
        if ($this->transferts->removeElement($transfert)) {
            // set the owning side to null (unless already changed)
            if ($transfert->getStock() === $this) {
                $transfert->setStock(null);
            }
        }

        return $this;
    }

   
}
