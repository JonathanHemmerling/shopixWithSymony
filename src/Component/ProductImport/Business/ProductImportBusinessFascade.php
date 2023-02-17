<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Business;

use App\Component\ProductImport\Business\Model\Import;
use App\Component\ProductImport\DTO\FilePathValueObject;

readonly class ProductImportBusinessFascade
{
    public function __construct(private Import $import)
    {

    }
    public function import(FilePathValueObject $filePathValueObject): void
    {
        $this->import->importFromCsv($filePathValueObject);
    }

}