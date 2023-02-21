<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Mapper\ProductsMapper;
use App\DTO\ProductsDataTransferObject;
use App\Repository\ProductsRepository;
use Predis\Client;

readonly class ProductHandler
{

    public function __construct(
        private ProductsMapper $productsMapper,
        private ProductsRepository $productsRepository,
        private Client $predisClient,
    ) {
    }

    public function sendProductAsJsonToRedis(int $productId): void
    {
        $product = $this->productsRepository->findOneBy(['id' => $productId]);
        $attributes = $product->getAttribute()->getValues();
        $attributesArray = [];
        foreach ($attributes as $attribute) {
            $attributesArray[] = $attribute->getAttribut();
        }
        $key = json_encode($product->getId());
        $value = json_encode([
            'category' => $product->getCategory()->getName(),
            'articleNumber' => $product->getArticleNumber(),
            'productName' => $product->getProductName(),
            'price' => $product->getPrice(),
            'description' => $product->getDescription(),
            'attribute' => $attributesArray,
        ]);
        $this->predisClient->set($key, $value);
    }

    public function getProductFromRedis(int $productId): ProductsDataTransferObject
    {
        $product = $this->predisClient->get($productId);
        $decodetProduct = json_decode($product);
        $productArray = [
            'attributes' => $decodetProduct->attribute,
            'category' => $decodetProduct->category,
            'articleNumber' => $decodetProduct->articleNumber,
            'productName' => $decodetProduct->productName,
            'price' => $decodetProduct->price,
            'description' => $decodetProduct->description,
        ];
        $productDTO = $this->productsMapper->mapToProductsDto(
            $productArray
        );
       return $productDTO;
    }

}