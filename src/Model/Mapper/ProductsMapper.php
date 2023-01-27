<?php

declare(strict_types=1);

namespace App\Model\Mapper;

use App\DTO\ProductsDataTransferObject;
use App\Entity\Product;

class ProductsMapper implements ProductsMapperInterface
{
    public function mapToProductsDto(Product|array $product): ProductsDataTransferObject
    {
        return new ProductsDataTransferObject(
            $product->getId(),
            $product->getMainId(),
            $product->getDisplayName(),
            $product->getProductName(),
            $product->getDescription(),
            $product->getPrice()
        );
    }
}