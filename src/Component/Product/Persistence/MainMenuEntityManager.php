<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\DTO\MainMenuDataTransferObject;
use App\Entity\MainCategorys;
use Doctrine\ORM\EntityManagerInterface;

class MainMenuEntityManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    //erst mit test abdecken
    public function create(MainMenuDataTransferObject $mainMenuData): void
    {
        $newCategory = new MainCategorys();
        $newCategory->setDisplayName($mainMenuData->displayName);
        $newCategory->setMainCategoryName($mainMenuData->mainCategoryName);
        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();
    }

    public function save(MainCategorys $mainCategorys, MainMenuDataTransferObject $mainMenuData): void
    {
        $newCategory = $mainCategorys;
        $newCategory->setDisplayName($mainMenuData->displayName);
        $newCategory->setMainCategoryName($mainMenuData->mainCategoryName);
        $this->entityManager->persist($newCategory);
        $this->entityManager->flush();
    }


}