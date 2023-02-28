<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Business\EntityImport;

use App\Component\Attributes\Mapper\AttributesMapper;
use League\Csv\MapIterator;
use Symfony\Component\Messenger\MessageBusInterface;

class AttributeImportCollection implements ImportCollectionInterface
{
    public function __construct(private AttributesMapper $attributesMapper, private readonly MessageBusInterface $messageBus)
    {
    }

    public function import(MapIterator $records): void
    {
        $arrayForAttributes = [];
        $attributeDTOs = [];
        foreach ($records as $record) {
            for ($i = 3; $i > 0; $i--) {
                if ($record['attr' . $i] !== 'n/a' && !empty($record['attr' . $i] && !in_array($record['attr' . $i], $arrayForAttributes)))
                {
                    $arrayForAttributes[$record['attr' . $i]] = [$record['attr' . $i]];
                }
            }
        }
        foreach ($arrayForAttributes as $attribute) {
            $attributeDTO = $this->attributesMapper->mapToAttributesDto($attribute[0]);
            $this->messageBus->dispatch($attributeDTO);
        }

    }
}