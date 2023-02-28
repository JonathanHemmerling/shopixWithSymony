<?php

declare(strict_types=1);

namespace App\Component\Productstorage\Business;

use App\Component\Productstorage\Business\Model\ProductStorage;
use App\Component\Productstorage\Mapper\RedisMapper;
use App\Repository\ProductsRepository;
use Predis\Client;

readonly class ProductstorageBusinessFascade
{
    public function __construct(
        private RedisMapper $redisMapper,
        private ProductStorage $productStorage,
        private ProductsRepository $productsRepository
    ) {
    }

    public function createRedisEntry(string $productName): void
    {
        $product = $this->productsRepository->findOneBy(['productName' => $productName]);
        $redisDTO = $this->redisMapper->mapToRedisDto(
            [
                'id' => $product->getId(),
                'attributes' => $product->getAttribute()->getValues(),
                'category' => $product->getCategory(),
                'articleNumber' => $product->getArticleNumber(),
                'productName' => $product->getProductName(),
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
            ]
        );
        $this->productStorage->sendProductAsDtoToRabbitMQ($product->getId());
        $this->productStorage->sendProductAsJsonToRedis($redisDTO);
    }

}