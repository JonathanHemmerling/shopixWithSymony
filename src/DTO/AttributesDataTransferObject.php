<?php

declare(strict_types=1);

namespace App\DTO;


class AttributesDataTransferObject
{
    public int|null $id = null;
    public string $attributeName = '';
    public string $attributeName1 = '';
    public string $attributeName2 = '';

    public function __construct()
    {
    }



}
