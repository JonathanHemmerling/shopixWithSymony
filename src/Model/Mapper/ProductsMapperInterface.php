<?php

declare(strict_types=1);

namespace App\Model\Mapper;

use App\DTO\ProductsDataTransferObject;
use App\Entity\Product;

interface ProductsMapperInterface
{
    public function mapToProductsDto(Product|array $product): ProductsDataTransferObject;
}