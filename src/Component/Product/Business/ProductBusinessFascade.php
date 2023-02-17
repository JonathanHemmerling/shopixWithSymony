<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\ProductsEntityManager;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Products;

readonly class ProductBusinessFascade
{
    public function __construct(private ProductsEntityManager $entityManager)
    {
    }
    public function create(ProductsDataTransferObject $productsDTO):void
    {
        $this->entityManager->create($productsDTO);
    }
    public function save(Products $product, ProductsDataTransferObject $productsDTO):void
    {
        $this->entityManager->save($product, $productsDTO);
    }
}