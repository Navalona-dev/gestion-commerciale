<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Exception\InvalidTypeException;
use App\Exception\PropertyVideException;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[Vich\Uploadable]
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


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoName = null;

    #[Vich\UploadableField(mapping:"application_logo", fileNameProperty:"logoName")]
    public ?File $logoFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

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

    /**
     * @var Collection<int, Transfert>
     */
    #[ORM\OneToMany(targetEntity: Transfert::class, mappedBy: 'application')]
    private Collection $transferts;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'application')]
    private Collection $notifications;

    /**
     * @var Collection<int, Facture>
     */
    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: 'application')]
    private Collection $factures;

    #[ORM\Column(nullable: true)]
    private ?string $nif = null;

    #[ORM\Column(nullable: true)]
    private ?string $stat = null;

    /**
     * @var Collection<int, Depense>
     */
    #[ORM\OneToMany(targetEntity: Depense::class, mappedBy: 'application')]
    private Collection $depenses;

    /**
     * @var Collection<int, Benefice>
     */
    #[ORM\OneToMany(targetEntity: Benefice::class, mappedBy: 'application')]
    private Collection $benefices;

    /**
     * @var Collection<int, Fourchette>
     */
    #[ORM\OneToMany(targetEntity: Fourchette::class, mappedBy: 'application')]
    private Collection $fourchettes;

    /**
     * @var Collection<int, Comptabilite>
     */
    #[ORM\OneToMany(targetEntity: Comptabilite::class, mappedBy: 'application')]
    private Collection $comptabilites;

    /**
     * @var Collection<int, MethodePaiement>
     */
    #[ORM\OneToMany(targetEntity: MethodePaiement::class, mappedBy: 'application')]
    private Collection $methodePaiements;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->userAppActive = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->produitCategories = new ArrayCollection();
        $this->produitTypes = new ArrayCollection();
        $this->transferts = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->depenses = new ArrayCollection();
        $this->benefices = new ArrayCollection();
        $this->fourchettes = new ArrayCollection();
        $this->comptabilites = new ArrayCollection();
        $this->methodePaiements = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->entreprise;
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
            $transfert->setApplication($this);
        }

        return $this;
    }

    public function removeTransfert(Transfert $transfert): static
    {
        if ($this->transferts->removeElement($transfert)) {
            // set the owning side to null (unless already changed)
            if ($transfert->getApplication() === $this) {
                $transfert->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setApplication($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getApplication() === $this) {
                $notification->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Facture>
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(Facture $facture): static
    {
        if (!$this->factures->contains($facture)) {
            $this->factures->add($facture);
            $facture->setApplication($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getApplication() === $this) {
                $facture->setApplication(null);
            }
        }

        return $this;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): static
    {
        $this->nif = $nif;

        return $this;
    }

    public function getStat(): ?string
    {
        return $this->stat;
    }

    public function setStat(?string $stat): static
    {
        $this->stat = $stat;

        return $this;
    }

    public function getLogoName(): ?string
    {
        return $this->logoName;
    }

    public function setLogoName(?string $logoName): self
    {
        $this->logoName = $logoName;

        return $this;
    }

    public function setLogoFile(File $logoFile )
    {
        $this->logoFile = $logoFile;

        if ($logoFile) {
            // Cela force le recalcul de la date de mise à jour
            $this->updatedAt = new \DateTimeImmutable();
        }
    }


    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Depense>
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): static
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses->add($depense);
            $depense->setApplication($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): static
    {
        if ($this->depenses->removeElement($depense)) {
            // set the owning side to null (unless already changed)
            if ($depense->getApplication() === $this) {
                $depense->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Benefice>
     */
    public function getBenefices(): Collection
    {
        return $this->benefices;
    }

    public function addBenefice(Benefice $benefice): static
    {
        if (!$this->benefices->contains($benefice)) {
            $this->benefices->add($benefice);
            $benefice->setApplication($this);
        }

        return $this;
    }

    public function removeBenefice(Benefice $benefice): static
    {
        if ($this->benefices->removeElement($benefice)) {
            // set the owning side to null (unless already changed)
            if ($benefice->getApplication() === $this) {
                $benefice->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Fourchette>
     */
    public function getFourchettes(): Collection
    {
        return $this->fourchettes;
    }

    public function addFourchette(Fourchette $fourchette): static
    {
        if (!$this->fourchettes->contains($fourchette)) {
            $this->fourchettes->add($fourchette);
            $fourchette->setApplication($this);
        }

        return $this;
    }

    public function removeFourchette(Fourchette $fourchette): static
    {
        if ($this->fourchettes->removeElement($fourchette)) {
            // set the owning side to null (unless already changed)
            if ($fourchette->getApplication() === $this) {
                $fourchette->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comptabilite>
     */
    public function getComptabilites(): Collection
    {
        return $this->comptabilites;
    }

    public function addComptabilite(Comptabilite $comptabilite): static
    {
        if (!$this->comptabilites->contains($comptabilite)) {
            $this->comptabilites->add($comptabilite);
            $comptabilite->setApplication($this);
        }

        return $this;
    }

    public function removeComptabilite(Comptabilite $comptabilite): static
    {
        if ($this->comptabilites->removeElement($comptabilite)) {
            // set the owning side to null (unless already changed)
            if ($comptabilite->getApplication() === $this) {
                $comptabilite->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MethodePaiement>
     */
    public function getMethodePaiements(): Collection
    {
        return $this->methodePaiements;
    }

    public function addMethodePaiement(MethodePaiement $methodePaiement): static
    {
        if (!$this->methodePaiements->contains($methodePaiement)) {
            $this->methodePaiements->add($methodePaiement);
            $methodePaiement->setApplication($this);
        }

        return $this;
    }

    public function removeMethodePaiement(MethodePaiement $methodePaiement): static
    {
        if ($this->methodePaiements->removeElement($methodePaiement)) {
            // set the owning side to null (unless already changed)
            if ($methodePaiement->getApplication() === $this) {
                $methodePaiement->setApplication(null);
            }
        }

        return $this;
    }
}
