<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use App\Exception\PropertyVideException;
use App\EntityManager\EntityFactory;
use App\Repository\CategoryofpermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
}
