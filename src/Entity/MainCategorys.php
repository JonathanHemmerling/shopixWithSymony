<?php

namespace App\Entity;


use App\Component\Product\Persistence\MainCategorysRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MainCategorysRepository::class)]
class MainCategorys
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    private ?string $mainCategoryName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMainCategoryName(): ?string
    {
        return $this->mainCategoryName;
    }

    public function setMainCategoryName(string $mainCategoryName): self
    {
        $this->mainCategoryName = $mainCategoryName;

        return $this;
    }

}
