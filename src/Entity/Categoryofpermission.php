<?php

namespace App\Entity;


use App\Entity\Permission;
use Doctrine\ORM\Mapping as ORM;
use App\EntityManager\EntityFactory;
use App\Exception\PropertyVideException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\CategoryofpermissionRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryofpermissionRepository::class)]
#[ORM\Table(name:'categoryofpermission')]
#[UniqueEntity(fields: ['title'], message: 'Ce titre est déjà utilisé. Veuillez en choisir un autre.')]
class Categoryofpermission
{
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;

    #[ORM\Column(name: "title", type: "string", nullable: false, unique: true)]
    private $title;

    #[ORM\Column(name: "description", type: "text")]
    private $description;

    /**
     * @var Collection<int, Facture>
     */
    #[ORM\OneToMany(targetEntity: Permission::class, mappedBy: 'categoryofpermission')]
    private Collection $permissions;

    public function __construct() {
        $this->permissions = new ArrayCollection();
    }
    
    public static function newCategoryofpermission($title = null) {

        if (is_null($title) or empty($title)) {
            throw new PropertyVideException("Your category title doesn't empty");
        }

        $instance = new Categoryofpermission();
        $instance->setTitle($title);

        return $instance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        if (is_null($title) or empty($title)) {
            throw new PropertyVideException("Your category title doesn't empty");
        }

        $this->title = $title;

        return $this;
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

     /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
            $permission->setCategorieofpermission($this);
        }

        return $this;
    }

    public function removeProduitCategory(Permission $permission): static
    {
        if ($this->permissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getCategorieofpermission() === $this) {
                $permission->setCategorieofpermission(null);
            }
        }

        return $this;
    }
}
