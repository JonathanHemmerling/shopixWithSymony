<?php

namespace App\Entity;


use App\Component\Product\Persistence\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $articleNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $productName = null;

    #[ORM\Column(nullable: true)]
    private ?int $price = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: Attributes::class, cascade: ["persist"])]
    private Collection $attr;

    public function __construct()
    {
        $this->attr = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticleNumber(): ?string
    {
        return $this->articleNumber;
    }

    public function setArticleNumber(?string $articleNumber): self
    {
        $this->articleNumber = $articleNumber;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

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
     * @return Collection<int, Attributes>
     */
    public function getAttr(): Collection
    {
        return $this->attr;
    }

    public function addAttr(Attributes $attr): self
    {
        if (!$this->attr->contains($attr)) {
            $this->attr->add($attr);
            $attr->setProducts($this);
        }

        return $this;
    }

    public function removeAttr(Attributes $attr): self
    {
        if ($this->attr->removeElement($attr)) {
            // set the owning side to null (unless already changed)
            if ($attr->getProducts() === $this) {
                $attr->setProducts(null);
            }
        }

        return $this;
    }
}
