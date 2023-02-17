<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Business\EntityImport;

use App\Component\Product\Mapper\ProductsMapper;
use App\Component\ProductImport\Communication\Dispatcher;
use League\Csv\MapIterator;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ProductImportCollection implements ImportCollectionInterface
{
    public function __construct(
        private ProductsMapper $productsMapper, private MessageBusInterface $messageBus,
    )
    {
    }

    public function import(MapIterator $records):void
    {
        foreach ($records as $record) {
            $attributesArrayForProducts = [];
            for ($i = 3; $i > 0; $i--) {
                if ($record['attr' . $i] !== 'n/a' && !empty(
                        $record['attr' . $i] && !in_array(
                            $record['attr' . $i],
                            $attributesArrayForProducts
                        )
                    )) {
                    $attributesArrayForProducts[$record['attr' . $i]] = $record['attr' . $i];
                }
            }

            $productsDTO = $this->productsMapper->mapToProductsDto(
                [
                    'attributes' => $attributesArrayForProducts,
                    'category' => $record['category'],
                    'articleNumber' => $record['art'],
                    'productName' => $record['product_name'],
                    'price' => (int)(str_replace(['€', ','], [''], $record['preis'])),
                    'description' => $record['desc'],
                ]
            );
            $this->messageBus->dispatch($productsDTO);
        }
    }
}