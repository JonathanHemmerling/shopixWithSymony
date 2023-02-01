<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\ProductsEntityManager;
use App\DTO\AttributesDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Attributes;
use App\Entity\Products;

class ProductsBusinessFascade
{
    public function __construct(private readonly ProductsEntityManager $entityManager)
    {
    }
    public function create(ProductsDataTransferObject $productsDTO, AttributesDataTransferObject $attributesDTO):void
    {
        $this->entityManager->create($productsDTO, $attributesDTO);
    }

    public function save(Products $product, Attributes $attributes, ProductsDataTransferObject $productsDTO, AttributesDataTransferObject $attributesDTO):void
    {
        $this->entityManager->save($product, $attributes, $productsDTO, $attributesDTO);
    }

}