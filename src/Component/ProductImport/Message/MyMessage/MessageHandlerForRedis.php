<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Message\MyMessage;

use App\Component\Productstorage\Persistence\ProductstorageEntityManager;
use App\DTO\RedisDataTransferObject;

class MessageHandlerForRedis
{
    public function __construct(private RedisDataTransferObject $redisDataTransferObject, private int $id, private ProductstorageEntityManager $entityManager)
    {
    }
    public function __invoke()
    {
        try {
            $this->entityManager->sendProductAsJsonToRedis($this->redisDataTransferObject, $this->id);
        }
        catch (\Throwable $e){
            dump($e);
        }

    }
}