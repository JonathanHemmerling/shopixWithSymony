<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Mapper\ProductsMapper;
use App\DTO\ProductsDataTransferObject;
use App\Repository\ProductsRepository;
use Predis\Client;

readonly class ProductHandler
{

    public function __construct(private ProductsMapper $productsMapper, private ProductsRepository $productsRepository, private Client $client)
    {

    }
    public function sendProductAsJsonToRedis(int $productId): void
    {
        $product = $this->productsRepository->findOneBy(['id' => $productId]);
        dd($product);
        $key = json_encode($product->getId());
        $value = json_encode(['category' => $product->getCategory(),
                'articleNumber' => $product->getArticleNumber(),
                'productName' => $product->getProductName(),
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
                'attribute' => $product->getAttribute()]);
        $this->client->set($key, $value);
    }

    public function getProductFromRedis(int $productId)//: ProductsDataTransferObject
    {
        $product = $this->client->get($productId);
        $decodet = json_decode($product);
        dd($decodet);
        $productDTO = $this->productsMapper->mapToProductsDto($decodet->attribute, $decodet->category, $decodet->articleNumber, $decodet->productName, $decodet->price, $decodet->description);
        dd($productDTO);
    }

}