<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\DTO\AttributesDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Attributes;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;

class ProductsEntityManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function create(ProductsDataTransferObject $productsDTO, AttributesDataTransferObject $attributesDTO)
    {
        $products = new Products();
        $attribute = new Attributes();
        $products->setArticleNumber($productsDTO->articleNumber);
        $products->setProductName($productsDTO->productName);
        $products->setPrice($productsDTO->price);
        $products->setCategory($productsDTO->category);
        $products->setDescription($productsDTO->description);
        $attribute->setAttributeName($attributesDTO->attributeName);
        $attribute->setAttributeName1($attributesDTO->attributeName1);
        $attribute->setAttributeName2($attributesDTO->attributeName2);
        $products->addAttr($attribute);
        $this->entityManager->persist($products);
        $this->entityManager->flush();
    }


    public function save(Products $product, Attributes $attributes, ProductsDataTransferObject $productsDTO, AttributesDataTransferObject $attributesDTO): void
    {
        $savedProduct = $product;
        $savedAttributes = $attributes;
        $savedProduct->setArticleNumber($productsDTO->articleNumber);
        $savedProduct->setProductName($productsDTO->productName);
        $savedProduct->setPrice($productsDTO->price);
        $savedProduct->setCategory($productsDTO->category);
        $savedProduct->setDescription($productsDTO->description);
        $savedAttributes->setAttributeName($attributesDTO->attributeName);
        $savedAttributes->setAttributeName($attributesDTO->attributeName1);
        $savedAttributes->setAttributeName($attributesDTO->attributeName2);
        $savedProduct->addAttr($savedAttributes);
        $this->entityManager->persist($savedProduct);
        $this->entityManager->flush();
    }


}