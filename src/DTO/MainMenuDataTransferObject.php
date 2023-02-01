<?php

declare(strict_types=1);

namespace App\DTO;

class MainMenuDataTransferObject
{
    public int|null $mainId = null;
    public string $mainCategoryName = '';
    public string $displayName = '';

    public function __construct()
    {
    }
}
