<?php

declare(strict_types=1);

namespace App\Component\Attributes\Mapper;

use App\DTO\AttributesDataTransferObject;

class AttributesMapper
{
    public function mapToAttributesDto(string $attributes): AttributesDataTransferObject
    {
        return new AttributesDataTransferObject($attributes,
        );
    }
}