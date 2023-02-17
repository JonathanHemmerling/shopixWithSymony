<?php

declare(strict_types=1);

namespace App\Component\ProductImport\DTO;

final readonly class FilePathValueObject
{
    public string $filePath;

    public function __construct(string $filePath)
    {
        if(!file_exists($filePath)) {
            throw new \RuntimeException(
                sprintf('File not found: %s' , $filePath)
            );
        }
        $this->filePath = $filePath;
    }
}