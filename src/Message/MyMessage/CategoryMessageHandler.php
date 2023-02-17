<?php

declare(strict_types=1);

namespace App\Message\MyMessage;

use App\Component\Category\Business\CategoryBusinessFascade;
use App\DTO\CategoryDataTransferObject;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CategoryMessageHandler
{
    public function __construct(private CategoryBusinessFascade $categoryBusinessFascade)
    {
    }
    public function __invoke(CategoryDataTransferObject $dto)
    {
        try {
            $this->categoryBusinessFascade->create($dto);
        }
        catch (\Throwable $e){
            dump($e);
        }

    }

}