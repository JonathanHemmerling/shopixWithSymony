<?php

declare(strict_types=1);

namespace App\Component\Productstorage\Business\Model;

use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Productstorage\Mapper\RedisMapper;
use App\DTO\ProductsDataTransferObject;
use App\DTO\RedisDataTransferObject;
use App\Repository\ProductsRepository;
use Predis\Client;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductStorage
{
    public function __construct(
        private ProductsMapper $productsMapper,
        private RedisMapper $redisMapper,
        private ProductsRepository $productsRepository,
        private Client $predisClient,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function sendProductAsDtoToRabbitMQ(int $productId):void
    {
        $product = $this->productsRepository->findOneBy(['id' => $productId]);
        $attributes = $product->getAttribute()->getValues();
        $attributesArray = [];
        foreach ($attributes as $attribute) {
            $attributesArray[] = $attribute->getAttribut();
        }
        $redisDTO = $this->redisMapper->mapToRedisDto(
            [
                'id' => $product->getId(),
                'attributes' => $attributesArray,
                'category' => $product->getCategory(),
                'articleNumber' => $product->getArticleNumber(),
                'productName' => $product->getProductName(),
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
            ]
        );
        $this->messageBus->dispatch($redisDTO);
    }

    public function sendProductAsJsonToRedis(RedisDataTransferObject $redisDataTransferObject): void
    {
        $key = self::buildKey($redisDataTransferObject->id);
        $encodetKey = json_encode($key);
        $value = json_encode([
            'category' => $redisDataTransferObject->category,
            'articleNumber' => $redisDataTransferObject->articleNumber,
            'productName' => $redisDataTransferObject->productName,
            'price' => $redisDataTransferObject->price,
            'description' => $redisDataTransferObject->description,
            'attributes' => $redisDataTransferObject->attributes,
        ]);
        $this->predisClient->set($encodetKey, $value);
    }

    public function getProductFromRedis(string $productId): ProductsDataTransferObject
    {
        $key = self::buildKey((int)$productId);
        $encodetKey = json_encode($key);
        $product = $this->predisClient->get($encodetKey);
        $decodetProduct = json_decode($product, true);
        $productDTO = $this->productsMapper->mapToProductsDto(
            $decodetProduct
        );
        return $productDTO;
    }

    private function buildKey(int $id): string
    {
        $key = 'Product:' . $id;
        return $key;
    }
}