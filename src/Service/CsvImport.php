<?php

declare(strict_types=1);

namespace App\Service;

use App\Component\Product\Business\ProductsBusinessFascade;
use App\DTO\AttributesDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Attributes;
use App\Entity\Products;

class CsvImport
{
    public function __construct(private readonly ProductsBusinessFascade $productsBusinessFascade)
    {
    }

    public function import(string $filePath): void
    {
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);

        while ($row = fgetcsv($file)) {
            $productsDTO = new ProductsDataTransferObject();
            $attributesDTO = new AttributesDataTransferObject();
            $productsDTO->articleNumber = $row[1];
            $productsDTO->productName = $row[2];
            $productsDTO->price = (int)(str_replace(['€' , ','] , [''] ,$row[3]));
            $productsDTO->category = $row[4];
            $productsDTO->description = $row[5];
            $attributesDTO->attributeName = $row[6];
            $attributesDTO->attributeName1 = $row[7];
            $attributesDTO->attributeName2 = $row[8];

            $this->productsBusinessFascade->create($productsDTO, $attributesDTO);
        }
    }

}