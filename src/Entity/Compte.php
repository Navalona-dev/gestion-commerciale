<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Gomyclic\Utilisateur;
use App\Repository\CompteRepository;
use App\Entity\Gomyclic\RappelCompte;
use App\Entity\Gomyclic\Cfa\BulletinCFA;
use App\Exception\PropertyVideException;
use App\Entity\Gomyclic\Rabelais\Diplome;
use App\Entity\Gomyclic\Cfa\ComptePeriode;
use App\Entity\Gomyclic\Cfa\EvaluationCFA;
use Doctrine\Common\Collections\Collection;
use App\Entity\Gomyclic\Rabelais\CompteMatiere;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Gomyclic\Cfa\EleveResponsableNote;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;


#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: CompteRepository::class)]
#[ORM\Table(name:'compte')]
class Compte
{
    const STATUT = [
        0 => 'Particulier',
        1 => 'SARL',
        2 => 'SAS',
        3 => 'SA',
        8 => 'SNC',
        9 => 'SCI',
        10 => 'Fondation',
        4 => 'Association',
        5 => 'Organisme public',
        6 => 'Entreprise individuelle',
        7 => 'Auto-Entrepreneur',
        8 => 'Société d’économie mixte',
    ];

    const GENRE = [
        0 => 'Prospect',
        1 => 'Client',
        2 => 'Fournisseur',
        5 => 'Suspect'
    ];

    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;

    #[ORM\Column(name: "nom", type: "string")]
    private $nom;

   
    #[ORM\Column(name: "genre", type: "integer", nullable: true)]
    private $genre;

    #[ORM\Column(name: "etat", type: "string", nullable: true)]
    private $etat;

    #[ORM\Column(name: "statut", type: "string", nullable: true)]
    private $statut;

    #[ORM\Column(name: "email", type: "string", nullable: true)]
    private $email;

    #[ORM\Column(name: "telephone", type: "string", nullable: true)]
    private $telephone;

    #[ORM\Column(name: "dateCreation", type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\Column(name: "dateModification", type: "datetime", nullable: true)]
    private $dateModification;

    #[ORM\Column(name: "nbAffaire", type: "integer", nullable: true)]
    private $nbAffaire;

    #[ORM\OneToMany(targetEntity: Affaire::class, mappedBy: 'compte', cascade:["remove"])]
    private $affaires;

   
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'comptes', cascade:["persist", "remove"])]
    private $utilisateur;

    
    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'comptes')]
    private $application;

    #[ORM\Column(name: "adresse", type: "string", nullable: true)]
    private $adresse;

    #[ORM\Column(name: "isLivraison", type: "boolean", nullable: true)]
    private $isLivraison;

    #[ORM\Column(name: "numero", type: "text", nullable: true)]
    private $numero;

    #[ORM\Column(name: "commentaire", type: "text", nullable: true)]
    private $commentaire;

    #[ORM\Column(name: "ca", type: "float", nullable: true)]
    private $ca;

    #[ORM\ManyToMany(targetEntity: Application::class, inversedBy: 'applicationComptes')]
    private $compteApplications;

    /**
     * @var Collection<int, ModeReglement>
     */
    #[ORM\OneToMany(targetEntity: ModeReglement::class, mappedBy: 'compte')]
    private Collection $modeReglements;

    /**
     * @var Collection<int, ProduitCategorie>
     */
    #[ORM\ManyToMany(targetEntity: ProduitCategorie::class, mappedBy: 'comptes')]
    private Collection $produitCategories;

    /**
     * @var Collection<int, Facture>
     */
    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: 'compte')]
    private Collection $factures;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $indiceFacture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $delaisPaiement = null;

    public function __construct()
    {
        $this->utilisateur = new ArrayCollection();
        $this->dateCreation = new \DateTime('now');
        $this->affaires = new ArrayCollection();
        $this->compteApplications = new ArrayCollection();
        $this->produitCategories = new ArrayCollection();
        $this->factures = new ArrayCollection();
        
    }

    public static function newCompte($instance = null)
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

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getGenre(): ?int
    {
        return $this->genre;
    }

    public function setGenre(?int $genre): self
    {
        $this->genre = $genre;

        return $this;
    }

    public function getGenreName(): ?string
    {
        return Compte::GENRE[$this->genre];
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(?int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getStatutName(): ?string
    {
        return Compte::STATUT[$this->statut];
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getGenreText()
    {
        if (isset(self::GENRE[$this->genre])) {
            return self::GENRE[$this->genre];
        }
        
        return $this->genre;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeInterface $dateModification): self
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    public function getNbAffaire(): ?int
    {
        return $this->nbAffaire;
    }

    public function setNbAffaire(?int $nbAffaire): self
    {
        $this->nbAffaire = $nbAffaire;

        return $this;
    }

    /**
     * @return Collection|Affaire[]
     */
    public function getAffaires(): Collection
    {
        return $this->affaires;
    }

    public function addAffaire(Affaire $affaire): self
    {
        if (!$this->affaires->contains($affaire)) {
            $this->affaires[] = $affaire;
            $affaire->setCompte($this);
        }

        return $this;
    }

    public function removeAffaire(Affaire $affaire): self
    {
        if ($this->affaires->contains($affaire)) {
            $this->affaires->removeElement($affaire);
            // set the owning side to null (unless already changed)
            if ($affaire->getCompte() === $this) {
                $affaire->setCompte(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }

    /**
     * @return Collection|User[]
     */
    public function getUtilisateur(): Collection
    {
        return $this->utilisateur;
    }

    public function addUtilisateur(User $utilisateur): self
    {
        if (!$this->utilisateur->contains($utilisateur)) {
            $this->utilisateur[] = $utilisateur;
        }

        return $this;
    }

    public function removeUtilisateur(User $utilisateur): self
    {
        if ($this->utilisateur->contains($utilisateur)) {
            $this->utilisateur->removeElement($utilisateur);
        }

        return $this;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }


    public function getIsLivraison(): ?bool
    {
        return $this->isLivraison;
    }

    public function setIsLivraison(?bool $isLivraison): self
    {
        $this->isLivraison = $isLivraison;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getCa(): ?float
    {
        return $this->ca;
    }

    public function setCa(?float $ca): self
    {
        $this->ca = $ca;

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getCompteApplications(): Collection
    {
        return $this->compteApplications;
    }

    public function addCompteApplication(Application $compteApplication): self
    {
        if (!$this->compteApplications->contains($compteApplication)) {
            $this->compteApplications[] = $compteApplication;
        }

        return $this;
    }

    public function removeCompteApplication(Application $compteApplication): self
    {
        $this->compteApplications->removeElement($compteApplication);

        return $this;
    }

    /**
     * @return Collection<int, ModeReglement>
     */
    public function getModeReglements(): Collection
    {
        return $this->modeReglements;
    }

    public function addModeReglement(ModeReglement $modeReglement): static
    {
        if (!$this->modeReglements->contains($modeReglement)) {
            $this->modeReglements->add($modeReglement);
            $modeReglement->setCompte($this);
        }

        return $this;
    }

    public function removeModeReglement(ModeReglement $modeReglement): static
    {
        if ($this->modeReglements->removeElement($modeReglement)) {
            // set the owning side to null (unless already changed)
            if ($modeReglement->getCompte() === $this) {
                $modeReglement->setCompte(null);
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
            $produitCategory->addCompte($this);
        }

        return $this;
    }

    public function removeProduitCategory(ProduitCategorie $produitCategory): static
    {
        if ($this->produitCategories->removeElement($produitCategory)) {
            $produitCategory->removeCompte($this);
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
            $facture->setCompte($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getCompte() === $this) {
                $facture->setCompte(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getIndiceFacture(): ?string
    {
        return $this->indiceFacture;
    }

    public function setIndiceFacture(?string $indiceFacture): static
    {
        $this->indiceFacture = $indiceFacture;

        return $this;
    }

    public function getDelaisPaiement(): ?string
    {
        return $this->delaisPaiement;
    }

    public function setDelaisPaiement(?string $delaisPaiement): static
    {
        $this->delaisPaiement = $delaisPaiement;

        return $this;
    }

   
}
