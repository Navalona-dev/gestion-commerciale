<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Service\AuthorizationManager;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name:'user')]
#[UniqueEntity(fields: ['email'], message: 'Cet e-mail est déjà utilisé. Veuillez en choisir un autre.')]
class User implements UserInterface, \Serializable, PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public static $ROLES = [
        self::ROLE_ADMIN,
    ];
    
    const CIVILITE = [
        0 => 'M.',
        //1 => 'Mrs',
        2 => 'Mme',
        //3 => 'Mlle'
    ];
    
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;


    #[ORM\Column(name: "email", type: "string", nullable: false, unique: true)]
    private $email;

    #[ORM\Column(name: "roles", type: "json")]
    private $roles = [];

    #[ORM\Column(name: "password", type: "string")]
    private $password;

   
    #[ORM\Column(name: "nom", type: "string", nullable: true)]
    private $nom;

    #[ORM\Column(name: "prenom", type: "string", nullable: true)]
    private $prenom;

    #[ORM\Column(name: "username", type: "string", nullable: true)]
    private $username;

    #[ORM\Column(name: "telephone", type: "string", nullable: true)]
    private $telephone;

    #[ORM\Column(name: "is_active", type: "boolean", nullable: true)]
    private $isActive;

    #[ORM\Column(name: "date_creation", type: "datetime")]
    private $DateCreation;

    #[ORM\Column(name: "date_modification", type: "datetime")]
    private $DateModification;

    #[ORM\ManyToMany(targetEntity: Privilege::class, inversedBy: 'users')]
    private $privileges;

    #[ORM\Column(name: "activation_token", type: "string", nullable: true)]
    private $activationToken;

    #[ORM\Column(name: "reset_token", type: "string", nullable: true)]
    private $resetToken;

    #[Vich\UploadableField(mapping: "user_image", fileNameProperty: "image")]
    private $imageFile;

    //#[ORM\Embedded(class: EmbeddedFile::class)]
    #[ORM\Column(name: "image", type: "string", nullable: true)]
    private $image;

    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private $updatedAt;

    /**
     * @var Collection<int, Application>
     */
    #[ORM\ManyToMany(targetEntity: Application::class, mappedBy: 'users')]
    private Collection $applications;

    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'userAppActive')]
    #[ORM\JoinColumn(name: "appActive_id", referencedColumnName: "id", onDelete: 'SET NULL')]
    private ?Application $appActive = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $civilite = null;

    #[ORM\ManyToMany(targetEntity: Compte::class, mappedBy: 'utilisateur')]
    private $comptes;

    public function __construct()
    {
        $this->privileges = new ArrayCollection();
        $this->DateCreation = new \DateTime('now',new \DateTimeZone('EAT'));
        $this->applications = new ArrayCollection();
        $this->comptes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        if (!is_null($password)) {
            $this->password = $password;
        }
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->DateCreation;
    }

    public function setDateCreation(\DateTimeInterface $DateCreation): self
    {
        $this->DateCreation = $DateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->DateModification;
    }

    public function setDateModification(?\DateTimeInterface $DateModification): self
    {
        $this->DateModification = $DateModification;

        return $this;
    }

    /**
     * @return Collection|Privilege[]
     */
    public function getPrivileges(): Collection
    {
        return $this->privileges;
    }

    public function addPrivilege(Privilege $privilege): self
    {
        if (!$this->privileges->contains($privilege)) {
            $this->privileges[] = $privilege;
        }

        return $this;
    }

    public function removePrivilege(Privilege $privilege): self
    {
        if ($this->privileges->contains($privilege)) {
            $this->privileges->removeElement($privilege);
        }

        return $this;
    }

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(string $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    public function setActivationToken(?string $activationToken): self
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getImage(): ?string
    {
        return $this->image;
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

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->email,
            $this->nom,
            $this->prenom,
            $this->telephone,
            $this->image,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->email,
            $this->nom,
            $this->prenom,
            $this->telephone,
            $this->image,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }

    public function isUserAllowedOnFeature(string $permissionTocheck)
    {
        return AuthorizationManager::isUserAllowedOnFeature($this, $permissionTocheck);
    }
    public function isUserGrantedPrivilege(string $privilegeTocheck)
    {
        return AuthorizationManager::isUserGrantedPrivilege($this, $privilegeTocheck);
    }
    public function isPrivilegeGrantedToUser(Privilege $privilege)
    {
        return AuthorizationManager::isPrivilegeGrantedToUser($privilege, $this);
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->addUser($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            $application->removeUser($this);
        }

        return $this;
    }

    public function getAppActive(): ?Application
    {
        return $this->appActive;
    }

    public function setAppActive(?Application $appActive): self
    {
        $this->appActive = $appActive;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getComptes(): Collection
    {
        return $this->comptes;
    }

    public function addCompte(Compte $compte): self
    {
        if (!$this->comptes->contains($compte)) {
            $this->comptes[] = $compte;
            $compte->addUtilisateur($this);
        }

        return $this;
    }

    public function removeCompte(Compte $compte): self
    {
        if ($this->comptes->contains($compte)) {
            $this->comptes->removeElement($compte);
            $compte->removeUtilisateur($this);
        }

        return $this;
    }

}
