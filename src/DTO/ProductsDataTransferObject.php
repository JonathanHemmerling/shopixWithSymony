<?php

declare(strict_types=1);

namespace App\DTO;



use App\Entity\Attributes;

class ProductsDataTransferObject
{
    public int|null $id = null;
    public string $articleNumber = '';
    public string $productName = '';
    public int $price = 0;
    public string $category = '';
    public string $description = '';

    public function __construct()
    {
    }



}
