<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Exception\PropertyVideException;
use App\Repository\PrivilegeRepository;

#[ORM\Entity(repositoryClass: PrivilegeRepository::class)]
#[ORM\Table(name:'privilege')]
#[UniqueEntity(fields: ['title'], message: 'Ce titre est déjà utilisé. Veuillez en choisir un autre.')]
class Privilege
{
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;

    #[ORM\Column(name: "title", type: "string", nullable: false, unique: true)]
    private $title;

    #[ORM\Column(name: "description", type: "text")]
    private $description;

    #[ORM\ManyToMany(targetEntity: Permission::class, inversedBy: 'privileges')]
    private $permissions;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'privileges')]
    private $users;

    public function __construct() {
        $this->permissions = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public static function  newPrivilege($title = null)
    {
        if (is_null($title) or empty($title)) {
            throw new PropertyVideException("Your privilege title doesn't empty");
        }

        $instance = new Privilege();
        $instance->setTitle($title);

        return $instance;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    /**
     * @return Collection|Permission[]
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->contains($permission)) {
            $this->permissions->removeElement($permission);
        }

        return $this;
    }
}
