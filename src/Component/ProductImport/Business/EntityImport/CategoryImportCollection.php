<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Business\EntityImport;

use App\Component\Category\Mapper\CategoryMapper;
use App\Component\ProductImport\Communication\Dispatcher;
use League\Csv\MapIterator;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class CategoryImportCollection implements ImportCollectionInterface
{

    public function __construct(
        private CategoryMapper $categoryMapper, private MessageBusInterface $messageBus
    )
    {
    }

    public function import(MapIterator $records): void
    {
        $categoryArray = [];
        foreach ($records as $record) {
            if (!in_array($record['category'], $categoryArray)) {
                $categoryArray[$record['category']] = ['name' => $record['category']];
            }
        }
        foreach ($categoryArray as $category) {
            $categoryDTO = $this->categoryMapper->mapToCategoryDto($category);
            $this->messageBus->dispatch($categoryDTO);
        }

    }
}