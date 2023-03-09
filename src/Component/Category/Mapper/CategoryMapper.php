<?php

declare(strict_types=1);

namespace App\Component\Category\Mapper;

use App\DTO\CategoryDataTransferObject;

class CategoryMapper
{
    public function mapToCategoryDto(array $category): CategoryDataTransferObject
    {
        return new CategoryDataTransferObject(
            $category['name'],
        );
    }
    public function mapToRedisCategoryDto(array $category): CategoryDataTransferObject
    {

        return new CategoryDataTransferObject(
            $category['name'],
            $category['id'],
        );
    }
}