<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\ProductEntityManager;
use App\DTO\ProductDataTransferObject;
use App\Entity\Product;

class ProductBusinessFascade
{
    public function __construct(private readonly ProductEntityManager $entityManager)
    {
    }

    public function create(ProductDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->create($dataTransferObject);
    }

    public function save(Product $product, ProductDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->save($product, $dataTransferObject);
    }

//save Product-> call entitymanager and save-> mit DTO-> DTO Übergeben an FORM

//Im Test von Entitymanager testen
}