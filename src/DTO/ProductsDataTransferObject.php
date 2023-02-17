<?php

declare(strict_types=1);

namespace App\DTO;

class ProductsDataTransferObject
{
    public function __construct(
        public array|null $attributes = [],
        public string $category = '',
        public string $articleNumber = '',
        public string $productName = '',
        public int $price = 0,
        public string $description = '',

    ) {
    }


}
