<?php

declare(strict_types=1);

namespace App\Component\Category\Persistence;

use App\DTO\CategoryDataTransferObject;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

readonly class CategoryEntityManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function create(CategoryDataTransferObject $category): void
    {
        $newCategory = new Category();
        $newCategory->setName($category->name);
        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();
    }

    public function save(Category $mainCategorys, CategoryDataTransferObject $mainMenuData): void
    {
        $newCategory = $mainCategorys;
        $newCategory->setName($mainMenuData->name);
        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();
    }


}