<?php

declare(strict_types=1);

namespace App\DTO;

class ProductDataTransferObject
{
    public int|null $productId = null;
    public int|null $mainId = null;
    public string $displayName = '';
    public string $productName = '';
    public string $description = '';
    public string $price = '';

    public function __construct()
    {
    }
}
