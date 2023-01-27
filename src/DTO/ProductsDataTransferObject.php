<?php

declare(strict_types=1);

namespace App\DTO;

class ProductsDataTransferObject
{
    public function __construct(
        public readonly int|null $productId,
        public readonly int|null $mainId,
        public readonly string|null $displayName,
        public readonly string|null $productName,
        public readonly string|null $description,
        public readonly string|null $price,
    ) {
    }
}
