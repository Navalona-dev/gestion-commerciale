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

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->userAppActive = new ArrayCollection();
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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setisActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
