<?php

declare(strict_types=1);

namespace App\Component\Category\Business;

use App\Component\Category\Persistence\CategoryEntityManager;
use App\DTO\CategoryDataTransferObject;
use App\Entity\Category;

readonly class CategoryBusinessFascade
{
    public function __construct(private CategoryEntityManager $entityManager)
    {
    }

    public function create(CategoryDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->create($dataTransferObject);
    }

    public function save(Category $categorys, CategoryDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->save($categorys, $dataTransferObject);
    }

//save Product-> call entitymanager and save-> mit DTO-> DTO Übergeben an FORM

//Im Test von Entitymanager testen
}