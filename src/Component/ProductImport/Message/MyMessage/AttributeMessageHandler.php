<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Message\MyMessage;

use App\Component\Attributes\Business\AttributesBusinessFascade;
use App\DTO\AttributesDataTransferObject;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class AttributeMessageHandler
{
    public function __construct(private  AttributesBusinessFascade $attributesBusinessFascade)
    {
    }
    public function __invoke(AttributesDataTransferObject $dto)
    {
        try {
            $this->attributesBusinessFascade->create($dto);
        }
        catch (\Throwable $e){
            dump($e);
        }

    }

}