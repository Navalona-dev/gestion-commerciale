<?php

namespace App\Entity;

use App\Exception\PropertyVideException;
use App\Repository\AffaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: AffaireRepository::class)]
#[ORM\Table(name:'affaire')]
class Affaire
{
    const STATUT = [
        'devis' => 'devis',
        'commande' => 'commande'
    ];

    const STATUT_FOURNISSEUR = [
        'commande' => 'commande',
    ];

    const PRESTATION = [
        'Vente' => 'Vente',
        'Abonnement' => 'Abonnement'
    ];

    const PAIEMENT = [
        'non' => 'Non payé',
        //'partiel' => 'Partiel',
        //'litige' => 'En litige',
        'paye' => 'Payé',
        'annule' => 'Annulé',
        'encours' => 'En cours',
        'enecheance' => 'En écheance',
        'endepot' => 'En dépôt'
    ];

    const DEVIS = [
        'encours' => 'En cours',
        'gagne' => 'Gagné',
        'perdu' => 'Perdu',
        //'sansSuite' => 'Sans suite',
        //'termine' => 'Terminé',
        //'envoye' => 'Envoyé'
    ];

    const ABONNEMENT = [
        'noncommence' => 'Non commencé',
        'encours' => 'En cours',
        'cloture' => 'Clôturé'
    ];

    const PERIODICITE = [
        'Mensuel' => 'mensuel',
        'Trimestriel' => 'trimestre',
        'Bi-annuel' => 'bi-annuel',
        'Annuel' => 'annuel',
        'Choisir sa période' => 'periode'
    ];

    const METHODE_PAIEMENT = [
        'bacs' => 'Virement bancaire',
        'cheque' => 'Paiements par chèque',
        'monetico' => 'Carte Bancaire'
    ];

    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Compte::class, inversedBy: 'affaires')]
    private $compte;

    #[ORM\Column(name: "nom", type: "string", nullable: true)]
    private $nom;

    #[ORM\Column(name: "prestation", type: "string", nullable: true)]
    private $prestation;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateCreation;

    #[ORM\Column(type: "string", nullable: true)]
    private $statut;

    #[ORM\Column(type: "string", nullable: true)]
    private $paiement;

    #[ORM\Column(type: "float", nullable: true)]
    private $ca;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateDevis;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateCommande;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateFacture;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateAnnule;

    #[ORM\Column(type: "float", nullable: true)]
    private $cout;

    #[ORM\Column(type: "float", nullable: true)]
    private $tva;

    #[ORM\Column(type: "float", nullable: true)]
    private $etatDevis;

    #[ORM\Column(type: "float", nullable: true)]
    private $etatCommande;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $isValide;

    #[ORM\Column(type: "float", nullable: true)]
    private $remise;

    #[ORM\Column(type: "float", nullable: true)]
    private $remisePourcent;

    #[ORM\Column(type: "text", nullable: true)]
    private $descriptif;


    #[ORM\Column(type: "string", nullable: true)]
    private $devisEvol;

    #[ORM\Column(type: "string", nullable: true)]
    private $numero;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'affaires')]
    private $application;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateLivraison;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $datePaiement;

    #[ORM\Column(type: "string", nullable: true)]
    private $abonnement;
    
    #[ORM\Column(type: "datetime", nullable: true)]
    private $dateModification;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'affaires')]
    private Collection $products;

    /**
     * @var Collection<int, Facture>
     */
    #[ORM\OneToMany(targetEntity: Facture::class, mappedBy: 'affaire')]
    private Collection $factures;

    #[ORM\ManyToOne]
    private ?Application $applicationRevendeur = null;

    #[ORM\Column(nullable: true)]
    private ?float $remiseProduit = null;
    
    #[ORM\Column(nullable: true)]
    private ?bool $depot = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isValid = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;


    public function __construct()
    {
        $this->dateCreation = new \DateTime('now');
        $this->paiement = 'non';
        $this->abonnement = 'noncommence';
        $this->products = new ArrayCollection();
        $this->factures = new ArrayCollection();
    }

    public static function newAffaire($instance, $statut = null, $compte = null)
    {
        if (is_null($instance->getNom()) or empty($instance->getNom())) {
            throw new PropertyVideException("Your name doesn't empty");
        }
        $date = new \DateTime();
        $affaire = new self();
        $affaire->setNom($instance);
        $affaire->setStatut($statut);
        $affaire->setCompte($compte);
        $affaire->setDevisEvol('encours');
        $affaire->setDateCreation($date);
        if ($statut == "devis") {
            $affaire->setDateDevis($date);
        } else {
            $affaire->setDateCommande($date);
        }
        return $affaire;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompte(): ?Compte
    {
        return $this->compte;
    }

    public function setCompte(?Compte $compte): self
    {
        $this->compte = $compte;

        return $this;
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

    public function getPrestation(): ?string
    {
        return $this->prestation;
    }

    public function setPrestation(?string $prestation): self
    {
        $this->prestation = $prestation;

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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getPaiement(): ?string
    {
        return $this->paiement;
    }

    public function getPaiementValue()
    {
        if (isset(self::PAIEMENT[$this->paiement])) {
            return self::PAIEMENT[$this->paiement];
        }

        return $this->paiement;
    }

    public function setPaiement(?string $paiement): self
    {
        $this->paiement = $paiement;

        return $this;
    }

    public function __toString()
    {
        return $this->getNom();
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

    public function getDateDevis(): ?\DateTimeInterface
    {
        return $this->dateDevis;
    }

    public function setDateDevis(?\DateTimeInterface $dateDevis): self
    {
        $this->dateDevis = $dateDevis;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(?\DateTimeInterface $dateCommande): self
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getDateFacture(): ?\DateTimeInterface
    {
        return $this->dateFacture;
    }

    public function setDateFacture(?\DateTimeInterface $dateFacture): self
    {
        $this->dateFacture = $dateFacture;

        return $this;
    }

    public function getDateAnnule(): ?\DateTimeInterface
    {
        return $this->dateAnnule;
    }

    public function setDateAnnule(?\DateTimeInterface $dateAnnule): self
    {
        $this->dateAnnule = $dateAnnule;

        return $this;
    }


    public function getCout(): ?float
    {
        return $this->cout;
    }

    public function setCout(?float $cout): self
    {
        $this->cout = $cout;

        return $this;
    }

    public function getTva(): ?float
    {
        return $this->tva;
    }

    public function setTva(?float $tva): self
    {
        $this->tva = $tva;

        return $this;
    }

    public function getEtatDevis(): ?float
    {
        return $this->etatDevis;
    }

    public function setEtatDevis(?float $etatDevis): self
    {
        $this->etatDevis = $etatDevis;

        return $this;
    }

    public function getEtatCommande(): ?float
    {
        return $this->etatCommande;
    }

    public function setEtatCommande(?float $etatCommande): self
    {
        $this->etatCommande = $etatCommande;

        return $this;
    }

    public function getIsValide(): ?bool
    {
        return $this->isValide;
    }

    public function setIsValide(?bool $isValide): self
    {
        $this->isValide = $isValide;

        return $this;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(?float $remise): self
    {
        $this->remise = $remise;

        return $this;
    }

    public function getRemisePourcent(): ?float
    {
        return $this->remisePourcent;
    }

    public function setRemisePourcent(?float $remisePourcent): self
    {
        $this->remisePourcent = $remisePourcent;

        return $this;
    }

    public function getDescriptif(): ?string
    {
        return $this->descriptif;
    }

    public function setDescriptif(?string $descriptif): self
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    public function getDevisEvol(): ?string
    {
        return $this->devisEvol;
    }

    public function setDevisEvol(?string $devisEvol): self
    {
        $this->devisEvol = $devisEvol;

        return $this;
    }

    public function getDevisEvolValue()
    {
        if (isset(self::DEVIS[$this->devisEvol])) {
            return self::DEVIS[$this->devisEvol];
        }

        return $this->devisEvol;
    }

    public function getPaiementName()
    {
        if (isset(self::PAIEMENT[$this->paiement])) {
            return self::PAIEMENT[$this->paiement];
        }

        return 'Non payé';
    }

    public function getAbonnementName()
    {
        if (isset(self::ABONNEMENT[$this->abonnement])) {
            return self::ABONNEMENT[$this->abonnement];
        }

        return 'Non commencé';
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

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

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function getConditionTexte(): ?string
    {
        return $this->conditionTexte;
    }

    public function setConditionTexte(?string $conditionTexte): self
    {
        $this->conditionTexte = $conditionTexte;

        return $this;
    }

    public function getNom2(): ?string
    {
        return $this->nom2;
    }

    public function setNom2(?string $nom2): self
    {
        $this->nom2 = $nom2;

        return $this;
    }

    public function getDateLivraison(): ?\DateTimeInterface
    {
        return $this->dateLivraison;
    }

    public function setDateLivraison(?\DateTimeInterface $dateLivraison): self
    {
        $this->dateLivraison = $dateLivraison;

        return $this;
    }

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    /**
     * @return string
     */
    public function getAbonnement(): ?string
    {
        return $this->abonnement;
    }

    /**
     * @param string|null $abonnement
     */
    public function setAbonnement(?string $abonnement): void
    {
        $this->abonnement = $abonnement;
    }

    public function getAbonnementValue()
    {
        if (isset(self::ABONNEMENT[$this->abonnement])) {
            return self::ABONNEMENT[$this->abonnement];
        } else {
            return self::ABONNEMENT['noncommence'];
        }

    }

    public function setDateDevisValue()
    {
        if (!$this->dateDevis and $this->dateCommande) {
            $this->dateDevis = $this->dateCommande;
        }
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
            $product->addAffaire($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeAffaire($this);
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
            $facture->setAffaire($this);
        }

        return $this;
    }

    public function removeFacture(Facture $facture): static
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getAffaire() === $this) {
                $facture->setAffaire(null);
            }
        }

        return $this;
    }

    public function getApplicationRevendeur(): ?Application
    {
        return $this->applicationRevendeur;
    }

    public function setApplicationRevendeur(?Application $applicationRevendeur): static
    {
        $this->applicationRevendeur = $applicationRevendeur;

        return $this;
    }

    public function isDepot(): ?bool
    {
        return $this->depot;
    }

    public function setDepot(?bool $depot): static
    {
        $this->depot = $depot;

        return $this;
    }

    public function getRemiseProduit(): ?float
    {
        return $this->remiseProduit;
    }

    public function setRemiseProduit(?float $remiseProduit): static
    {
        $this->remiseProduit = $remiseProduit;

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

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;

        return $this;
    }

  
}
