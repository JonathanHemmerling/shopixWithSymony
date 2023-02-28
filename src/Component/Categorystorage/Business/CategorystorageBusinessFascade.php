<?php

declare(strict_types=1);

namespace App\Component\Categorystorage\Business;

use App\Component\Category\Mapper\CategoryMapper;
use App\Component\Categorystorage\Business\Model\Categorystorage;
use App\Component\Categorystorage\Mapper\RedisMapper;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Predis\Client;

readonly class CategorystorageBusinessFascade
{
    public function __construct(
        private CategoryMapper $categoryMapper,
        private CategoryStorage $categorystorage,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function createRedisEntry(int $id): void
    {
        $categoryDTO = $this->categorystorage->sendCategoryAsDtoToRabbitMQ($id);
        $this->categorystorage->sendCategoryAsJsonToRedis($categoryDTO);
    }
}