<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\ProductEntityManager;
use App\DTO\ProductsDataTransferObject;

class ProductBusinessFascade
{
    public function __construct(private readonly ProductEntityManager $entityManager)
    {
    }

    public function create(ProductsDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->create($dataTransferObject);
    }

    public function save(ProductsDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->save($dataTransferObject);
    }

//save Product-> call entitymanager and save-> mit DTO-> DTO Übergeben an FORM

//Im Test von Entitymanager testen
}