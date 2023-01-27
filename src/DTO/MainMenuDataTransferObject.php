<?php

declare(strict_types=1);

namespace App\DTO;

class MainMenuDataTransferObject
{
    public function __construct(
        public readonly int|null $mainId,
        public readonly string $mainCategoryName,
        public readonly string $displayName,
    ) {
    }
}
