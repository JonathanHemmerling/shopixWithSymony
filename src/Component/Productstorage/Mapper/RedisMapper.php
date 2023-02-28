<?php

declare(strict_types=1);

namespace App\Component\Productstorage\Mapper;

use App\DTO\ProductsDataTransferObject;
use App\DTO\RedisDataTransferObject;

class RedisMapper
{
    public function mapToRedisDto(array $product): RedisDataTransferObject
    {
        return new RedisDataTransferObject(
            $product['id'],
            $product['attributes'],
            $product['category']->getName(),
            $product['articleNumber'],
            $product['productName'],
            $product['price'],
            $product['description'],
        );
    }
}