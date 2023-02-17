<?php

declare(strict_types=1);

namespace App\Component\Product\Mapper;

use App\DTO\ProductsDataTransferObject;

class ProductsMapper
{
    public function mapToProductsDto(array $product): ProductsDataTransferObject
    {
        return new ProductsDataTransferObject(
            $product['attributes'],
            $product['category'],
            $product['articleNumber'],
            $product['productName'],
            $product['price'],
            $product['description'],
        );
    }
}