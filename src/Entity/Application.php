<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Exception\InvalidTypeException;
use App\Exception\PropertyVideException;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $entreprise = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'applications')]
    private Collection $users;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'appActive')]
    private Collection $userAppActive;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomResp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomResp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mailResp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'application')]
    private Collection $products;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\OneToMany(targetEntity: Categorie::class, mappedBy: 'application')]
    private Collection $categories;

    /**
     * @var Collection<int, ProduitCategorie>
     */
    #[ORM\OneToMany(targetEntity: ProduitCategorie::class, mappedBy: 'application')]
    private Collection $produitCategories;

    /**
     * @var Collection<int, ProduitType>
     */
    #[ORM\OneToMany(targetEntity: ProduitType::class, mappedBy: 'application')]
    private Collection $produitTypes;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->userAppActive = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->produitCategories = new ArrayCollection();
        $this->produitTypes = new ArrayCollection();
    }

    public static function newApplicationFromInstance($instance = null)
    {
        if (is_null($instance->getEntreprise()) or empty($instance->getEntreprise())) {
            throw new PropertyVideException("Your permission title doesn't empty");
        }

        return $instance;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(string $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserAppActive(): Collection
    {
        return $this->userAppActive;
    }

    public function addUserAppActive(User $userAppActive): static
    {
        if (!$this->userAppActive->contains($userAppActive)) {
            $this->userAppActive->add($userAppActive);
            $userAppActive->getAppActive($this);
        }

        return $this;
    }

    public function removeUserAppActive(User $userAppActive): static
    {
        if ($this->userAppActive->removeElement($userAppActive)) {
            // set the owning side to null (unless already changed)
            if ($userAppActive->getAppActive() === $this) {
                $userAppActive->setAppActive(null);
            }
        }

        return $this;
    }

    public function getNomResp(): ?string
    {
        return $this->nomResp;
    }

    public function setNomResp(?string $nomResp): static
    {
        $this->nomResp = $nomResp;

        return $this;
    }

    public function getPrenomResp(): ?string
    {
        return $this->prenomResp;
    }

    public function setPrenomResp(?string $prenomResp): static
    {
        $this->prenomResp = $prenomResp;

        return $this;
    }

    public function getMailResp(): ?string
    {
        return $this->mailResp;
    }

    public function setMailResp(?string $mailResp): static
    {
        $this->mailResp = $mailResp;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setisActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setApplication($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getApplication() === $this) {
                $product->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categorie $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setApplication($this);
        }

        return $this;
    }

    public function removeCategory(Categorie $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getApplication() === $this) {
                $category->setApplication(null);
            }
        }

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
            $produitCategory->setApplication($this);
        }

        return $this;
    }

    public function removeProduitCategory(ProduitCategorie $produitCategory): static
    {
        if ($this->produitCategories->removeElement($produitCategory)) {
            // set the owning side to null (unless already changed)
            if ($produitCategory->getApplication() === $this) {
                $produitCategory->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProduitType>
     */
    public function getProduitTypes(): Collection
    {
        return $this->produitTypes;
    }

    public function addProduitType(ProduitType $produitType): static
    {
        if (!$this->produitTypes->contains($produitType)) {
            $this->produitTypes->add($produitType);
            $produitType->setApplication($this);
        }

        return $this;
    }

    public function removeProduitType(ProduitType $produitType): static
    {
        if ($this->produitTypes->removeElement($produitType)) {
            // set the owning side to null (unless already changed)
            if ($produitType->getApplication() === $this) {
                $produitType->setApplication(null);
            }
        }

        return $this;
    }
}
