<?php

declare(strict_types=1);

namespace App\DTO;

class CategoryDataTransferObject
{
    public function __construct(public string $name = '', public int|null $id = null)
    {
    }
}