<?php

declare(strict_types=1);

namespace App\Component\Categorystorage\Business\Model;

use App\Component\Category\Mapper\CategoryMapper;
use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Categorystorage\Mapper\RedisMapper;
use App\DTO\CategoryDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\DTO\RedisDataTransferObject;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Predis\Client;
use Symfony\Component\Messenger\MessageBusInterface;

class CategoryStorage
{
    public function __construct(
        private CategoryMapper $categoryMapper,
        private CategoryRepository $categoryRepository,
        private Client $predisClient,
        private MessageBusInterface $messageBus,
        private ProductsRepository $productsRepository,
    ) {
    }

    public function sendCategoryAsDtoToRabbitMQ(int $categoryId):CategoryDataTransferObject
    {
        $category = $this->categoryRepository->findOneBy(['id' => $categoryId]);
        $products = $this->productsRepository->findBy(['category' => $categoryId]);
        dd($products);
        $categoryDTO = $this->categoryMapper->mapToRedisCategoryDto(['name' => $category->getName(), 'id' => $category->getId(), 'products' => $category->getProducts()->getValues()]);
        $this->messageBus->dispatch($categoryDTO);
        return $categoryDTO;
    }

    public function sendCategoryAsJsonToRedis(CategoryDataTransferObject $categoryDataTransferObject): void
    {
        $key = self::buildKey($categoryDataTransferObject->id);
        $encodetKey = json_encode($key);
        $value = json_encode([
            'name' => $categoryDataTransferObject->name,
            'products' => $categoryDataTransferObject->products,
        ]);
        $this->predisClient->set($encodetKey, $value);
    }
    public function getCategoryFromRedis(string $categoryId): CategoryDataTransferObject
    {
        $key = self::buildKey((int)$categoryId);
        $encodetKey = json_encode($key);
        $category = $this->predisClient->mget($encodetKey);
        $decodetCategory = json_decode($category, true);
        $categoryDTO = $this->categoryMapper->mapToCategoryDto(
            $decodetCategory
        );
        return $categoryDTO;
    }
    private function buildKey(int $id): string
    {
        $key = 'Category:' . $id;
        return $key;
    }
}