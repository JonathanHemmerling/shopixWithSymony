<?php

namespace App\Entity;

use App\Component\Product\Persistence\AttributesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributesRepository::class)]
class Attributes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $attributeName = null;

    #[ORM\Column(length: 255)]
    private ?string $attributeName1 = null;

    #[ORM\Column(length: 255)]
    private ?string $attributeName2 = null;

    #[ORM\ManyToOne(cascade: ["persist"], inversedBy: 'attr')]
    private ?Products $products = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttributeName(): ?string
    {
        return $this->attributeName;
    }

    public function setAttributeName(string $attributeName): self
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    public function getAttributeName1(): ?string
    {
        return $this->attributeName1;
    }

    public function setAttributeName1(string $attributeName1): self
    {
        $this->attributeName1 = $attributeName1;

        return $this;
    }
    public function getAttributeName2(): ?string
    {
        return $this->attributeName2;
    }

    public function setAttributeName2(string $attributeName2): self
    {
        $this->attributeName2 = $attributeName2;

        return $this;
    }
    public function getProducts(): ?Products
    {
        return $this->products;
    }

    public function setProducts(?Products $products): self
    {
        $this->products = $products;

        return $this;
    }
}
