<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Exception\PropertyVideException;
use App\Repository\ProduitTypeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ProduitTypeRepository::class)]
class ProduitType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'produitTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Application $application = null;

    /**
     * @var Collection<int, ProduitCategorie>
     */
    #[ORM\OneToMany(targetEntity: ProduitCategorie::class, mappedBy: 'type')]
    private Collection $produitCategories;

    public function __construct()
    {
        $this->produitCategories = new ArrayCollection();
    }

    public static function newProduitType($instance = null)
    {
        if (is_null($instance->getNom()) or empty($instance->getNom())) {
            throw new PropertyVideException("Your name doesn't empty");
        }

        return $instance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
     * @return Collection<int, ProduitCategorie>
     */
    public function getProduitCategories(): Collection
    {
        return $this->produitCategories;
    }

    public function addProduitCategory(ProduitCategorie $produitCategory): static
    {
        if (!$this->produitCategories->contains($produitCategory)) {
            $this->produitCategories->add($produitCategory);
            $produitCategory->setType($this);
        }

        return $this;
    }

    public function removeProduitCategory(ProduitCategorie $produitCategory): static
    {
        if ($this->produitCategories->removeElement($produitCategory)) {
            // set the owning side to null (unless already changed)
            if ($produitCategory->getType() === $this) {
                $produitCategory->setType(null);
            }
        }

        return $this;
    }
}
