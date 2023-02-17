<?php

declare(strict_types=1);

namespace App\Mapper;

use App\DTO\CategoryDataTransferObject;

class CategoryMapper
{
    public function mapToCategoryDto(string $category): CategoryDataTransferObject
    {
        return new CategoryDataTransferObject(
            $category
        );
    }
}