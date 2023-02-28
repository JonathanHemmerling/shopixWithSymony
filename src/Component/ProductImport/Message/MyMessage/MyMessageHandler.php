<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Message\MyMessage;

use App\Component\Product\Business\ProductBusinessFascade;
use App\DTO\ProductsDataTransferObject;
use App\Message\MyMessageDto;
use App\Message\ProductMessageDto;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class MyMessageHandler
{
    public function __construct(private ProductBusinessFascade $productBusinessFascade)
    {
    }
    public function __invoke(ProductsDataTransferObject $dto)
    {
        try {
            $this->productBusinessFascade->create($dto);
        }
        catch (\Throwable $e){
            dump($e);
        }

    }

}