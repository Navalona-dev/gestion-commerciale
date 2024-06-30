<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Exception\PropertyVideException;
use App\Repository\ProductImageRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
#[Vich\Uploadable]
class ProductImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping:"product_image", fileNameProperty:"image")]
    public ?File $imageFile = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\ManyToOne(inversedBy: 'productImages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProduitCategorie $produitCategorie = null;

    public function __construct()
    {
        $this->produitCategories = new ArrayCollection();
    }

    public static function newProduitImage($instance = null)
    {
        /*if (is_null($instance->getImage()) or empty($instance->getImage())) {
            throw new PropertyVideException("Your image doesn't empty");
        }*/

        return $instance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function setImageFile(File $image )
    {
        $this->imageFile = $image;
    }


    public function getImageFile()
    {
        return $this->imageFile;
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

    public function getProduitCategorie(): ?ProduitCategorie
    {
        return $this->produitCategorie;
    }

    public function setProduitCategorie(?ProduitCategorie $produitCategorie): static
    {
        $this->produitCategorie = $produitCategorie;

        return $this;
    }
}
