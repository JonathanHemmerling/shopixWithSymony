<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Business\Model;

use App\Component\ProductImport\Business\EntityImport\ImportCollectionInterface;
use App\Component\ProductImport\DTO\FilePathValueObject;
use League\Csv\Reader;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class Import
{
    /**
     * @param ImportCollectionInterface[] $imports
     */
    public function __construct(
        #[TaggedIterator('import.all_imports')]
        private iterable $imports,
    ) {
    }

    public function importFromCsv(
        FilePathValueObject $filePathValueObject,
    ): void {
        $file = fopen($filePathValueObject->filePath, 'r');
        $header = fgetcsv($file);
        $csv = Reader::createFromPath($filePathValueObject->filePath, 'r');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();
        $records = $csv->getRecords();
        foreach ($this->imports as $import) {
            $import->import($records);
        }
    }
}