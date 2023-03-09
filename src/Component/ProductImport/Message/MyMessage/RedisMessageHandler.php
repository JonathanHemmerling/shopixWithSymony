<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Message\MyMessage;

use App\Component\Product\Business\ProductBusinessFascade;
use App\Component\Productstorage\Persistence\ProductstorageEntityManager;
use App\DTO\RedisDataTransferObject;

class RedisMessageHandler
{
    public function __construct(private ProductBusinessFascade $productBusinessFascade)
    {
    }
    public function __invoke(RedisDataTransferObject $dto)
    {
        try {
            $this->productBusinessFascade->createRedis($dto);
        }
        catch (\Throwable $e){
            dump($e);
        }
    }
}