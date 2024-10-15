<?php

namespace App\Entity;

use App\Exception\PropertyVideException;
use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
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

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Compte $compte = null;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Affaire $affaire = null;

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
    private ?string $numeroCommande = null;

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

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Application $application = null;

    /**
     * @var Collection<int, FactureDetail>
     */
    #[ORM\OneToMany(targetEntity: FactureDetail::class, mappedBy: 'facture')]
    private Collection $factureDetails;

    /**
     * @var Collection<int, ReglementFacture>
     */
    #[ORM\OneToMany(targetEntity: ReglementFacture::class, mappedBy: 'facture')]
    private Collection $reglementFactures;

    /**
     * @var Collection<int, FactureEcheance>
     */
    #[ORM\OneToMany(targetEntity: FactureEcheance::class, mappedBy: 'facture')]
    private Collection $factureEcheances;

    #[ORM\Column(nullable: true)]
    private ?bool $isEcheance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avance = null;

    #[ORM\Column(nullable: true)]
    private ?int $echeanceNumero = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateReglement = null;

    #[ORM\Column(nullable: true)]
    private ?int $depotNumero = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isDepot = null;

    /**
     * @var Collection<int, MethodePaiement>
     */
    #[ORM\OneToMany(targetEntity: MethodePaiement::class, mappedBy: 'facture')]
    private Collection $methodePaiements;

    #[ORM\ManyToOne(inversedBy: 'factures')]
    private ?Benefice $benefice = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isBenefice = null;

    public function __construct()
    {
        $this->factureDetails = new ArrayCollection();
        $this->reglementFactures = new ArrayCollection();
        $this->factureEcheances = new ArrayCollection();
        $this->methodePaiements = new ArrayCollection();
    }

    public static function newFacture($affaire = null)
    {
        /*if (is_null($affaire->getNom()) or empty($affaire->getNom())  or is_null($affaire->getCompte()->getNom()) or empty($affaire->getCompte()->getNom())) {
            throw new PropertyVideException("Your name doesn't empty");
        }*/
        $facture = new self();
        $facture->setAffaire($affaire);
        $facture->setCompte($affaire->getCompte());
        return $facture;
    }

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

    public function getCompte(): ?Compte
    {
        return $this->compte;
    }

    public function setCompte(?Compte $compte): static
    {
        $this->compte = $compte;

        return $this;
    }

    public function getAffaire(): ?Affaire
    {
        return $this->affaire;
    }

    public function setAffaire(?Affaire $affaire): static
    {
        $this->affaire = $affaire;

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

    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    public function setNumeroCommande(?string $numeroCommande): static
    {
        $this->numeroCommande = $numeroCommande;

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

    /**
     * @return Collection<int, FactureDetail>
     */
    public function getFactureDetails(): Collection
    {
        return $this->factureDetails;
    }

    public function addFactureDetail(FactureDetail $factureDetail): static
    {
        if (!$this->factureDetails->contains($factureDetail)) {
            $this->factureDetails->add($factureDetail);
            $factureDetail->setFacture($this);
        }

        return $this;
    }

    public function removeFactureDetail(FactureDetail $factureDetail): static
    {
        if ($this->factureDetails->removeElement($factureDetail)) {
            // set the owning side to null (unless already changed)
            if ($factureDetail->getFacture() === $this) {
                $factureDetail->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReglementFacture>
     */
    public function getReglementFactures(): Collection
    {
        return $this->reglementFactures;
    }

    public function addReglementFacture(ReglementFacture $reglementFacture): static
    {
        if (!$this->reglementFactures->contains($reglementFacture)) {
            $this->reglementFactures->add($reglementFacture);
            $reglementFacture->setFacture($this);
        }

        return $this;
    }

    public function removeReglementFacture(ReglementFacture $reglementFacture): static
    {
        if ($this->reglementFactures->removeElement($reglementFacture)) {
            // set the owning side to null (unless already changed)
            if ($reglementFacture->getFacture() === $this) {
                $reglementFacture->setFacture(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FactureEcheance>
     */
    public function getFactureEcheances(): Collection
    {
        return $this->factureEcheances;
    }

    public function addFactureEcheance(FactureEcheance $factureEcheance): static
    {
        if (!$this->factureEcheances->contains($factureEcheance)) {
            $this->factureEcheances->add($factureEcheance);
            $factureEcheance->setFacture($this);
        }

        return $this;
    }

    public function removeFactureEcheance(FactureEcheance $factureEcheance): static
    {
        if ($this->factureEcheances->removeElement($factureEcheance)) {
            // set the owning side to null (unless already changed)
            if ($factureEcheance->getFacture() === $this) {
                $factureEcheance->setFacture(null);
            }
        }

        return $this;
    }

    public function isEcheance(): ?bool
    {
        return $this->isEcheance;
    }

    public function setEcheance(?bool $isEcheance): static
    {
        $this->isEcheance = $isEcheance;

        return $this;
    }

    public function getAvance(): ?string
    {
        return $this->avance;
    }

    public function setAvance(?string $avance): static
    {
        $this->avance = $avance;

        return $this;
    }

    public function getEcheanceNumero(): ?int
    {
        return $this->echeanceNumero;
    }

    public function setEcheanceNumero(?int $echeanceNumero): static
    {
        $this->echeanceNumero = $echeanceNumero;

        return $this;
    }

    public function getDateReglement(): ?\DateTimeInterface
    {
        return $this->dateReglement;
    }

    public function setDateReglement(?\DateTimeInterface $dateReglement): static
    {
        $this->dateReglement = $dateReglement;

        return $this;
    }

    public function getDepotNumero(): ?int
    {
        return $this->depotNumero;
    }

    public function setDepotNumero(?int $depotNumero): static
    {
        $this->depotNumero = $depotNumero;

        return $this;
    }

    public function isDepot(): ?bool
    {
        return $this->isDepot;
    }

    public function setDepot(?bool $isDepot): static
    {
        $this->isDepot = $isDepot;

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
            $methodePaiement->setFacture($this);
        }

        return $this;
    }

    public function removeMethodePaiement(MethodePaiement $methodePaiement): static
    {
        if ($this->methodePaiements->removeElement($methodePaiement)) {
            // set the owning side to null (unless already changed)
            if ($methodePaiement->getFacture() === $this) {
                $methodePaiement->setFacture(null);
            }
        }

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

    public function isBenefice(): ?bool
    {
        return $this->isBenefice;
    }

    public function setIsBenefice(?bool $isBenefice): static
    {
        $this->isBenefice = $isBenefice;

        return $this;
    }


}
