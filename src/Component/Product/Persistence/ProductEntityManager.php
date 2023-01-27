<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\DTO\ProductsDataTransferObject;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductEntityManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    //erst mit test abdecken
    public function create(ProductsDataTransferObject $productData): void
    {
        $newProduct = new Product();
        $newProduct->setMainId($productData->mainId);
        $newProduct->setDisplayName($productData->displayName);
        $newProduct->setProductName($productData->productName);
        $newProduct->setDescription($productData->description);
        $newProduct->setPrice($productData->price);
        $this->entityManager->persist($newProduct);
        $this->entityManager->flush();
    }

    public function save(ProductsDataTransferObject $productData): void
    {
        $savedProduct = new Product();
        $savedProduct->setMainId($productData->mainId);
        $savedProduct->setDisplayName($productData->displayName);
        $savedProduct->setProductName($productData->productName);
        $savedProduct->setDescription($productData->description);
        $savedProduct->setPrice($productData->price);
        $this->entityManager->persist($savedProduct);
        $this->entityManager->flush();
    }


}