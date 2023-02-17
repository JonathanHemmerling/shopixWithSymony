<?php

declare(strict_types=1);

namespace App\Component\Attributes\Business;

use App\Component\Attributes\Persistence\AttributesEntityManager;
use App\DTO\AttributesDataTransferObject;
use App\Entity\Attributes;

readonly class AttributesBusinessFascade
{
    public function __construct(private AttributesEntityManager $entityManager)
    {
    }

    public function create(AttributesDataTransferObject $attributesDTO):void
    {
        $this->entityManager->create($attributesDTO);
    }

    public function save(Attributes $attributes, AttributesDataTransferObject $attributesDTO):void
    {
        $this->entityManager->save($attributes, $attributesDTO);
    }

}