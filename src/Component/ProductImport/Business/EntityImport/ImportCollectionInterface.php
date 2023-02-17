<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Business\EntityImport;

use League\Csv\MapIterator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('import.all_imports')]
interface ImportCollectionInterface
{
    public function import(MapIterator $records);
}