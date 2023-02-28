<?php

declare(strict_types=1);

namespace App\Component\Categorystorage\Mapper;

use App\DTO\CategoryDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\DTO\RedisDataTransferObject;

class RedisMapper
{
    public function mapToRedisDto(array $category): CategoryDataTransferObject
    {
        return new CategoryDataTransferObject(
            $category['name'],
            $category['id'],
        );
    }
}