<?php

declare(strict_types=1);

namespace App\DTO;

class AttributesDataTransferObject
{

    public function __construct(public string $attribute = '', public int|null $id = null,
    ){
    }


}