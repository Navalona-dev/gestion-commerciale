<?php

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Exception\InvalidTypeException;
use App\Exception\UniqPropertyException;
use Doctrine\Common\Persistence\ObjectManager;
use App\Exception\PropertyVideException;
use App\Repository\PermissionRepository;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name:'permission')]
#[UniqueEntity(fields: ['title'], message: 'Ce titre est déjà utilisé. Veuillez en choisir un autre.')]
class Permission
{
    #[ORM\Column(name: "id", type: "integer", nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;

    #[ORM\Column(name: "title", type: "string", nullable: false, unique: true)]
    private $title;

    #[ORM\Column(name: "description", type: "text")]
    private $description;

    #[ORM\ManyToOne(inversedBy: 'permissions', targetEntity: Categoryofpermission::class)]
    #[ORM\JoinColumn(nullable: false, name: 'categoryofpermission_id')]
    private $categoryofpermission;

    #[ORM\ManyToMany(targetEntity: Privilege::class, mappedBy: 'permissions')]
    private $privileges;


    public function  __construct()
    {
        $this->privileges = new ArrayCollection();
    }

    public static function newPermission($categoryofpermission = null, $title = null)
    {
        if (is_null($title) or empty($title)) {
            throw new PropertyVideException("Your permission title doesn't empty");
        }

        if (!$categoryofpermission instanceof Categoryofpermission) {
            throw new InvalidTypeException("Your category  entity is not valid");
        }

        $instance = new Permission();
        $instance->setCategoryofpermission($categoryofpermission);
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

    public function getCategoryofpermission(): ?Categoryofpermission
    {
        return $this->categoryofpermission;
    }

    public function setCategoryofpermission(?Categoryofpermission $categoryofpermission): self
    {
        $this->categoryofpermission = $categoryofpermission;

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
}
