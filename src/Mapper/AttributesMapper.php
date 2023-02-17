<?php

declare(strict_types=1);

namespace App\Mapper;

use App\DTO\AttributesDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Products;
use Doctrine\Common\Collections\ArrayCollection;

class AttributesMapper
{
    public function mapToAttributesDto(string $attributes): AttributesDataTransferObject
    {
        return new AttributesDataTransferObject($attributes,
        );
    }
}