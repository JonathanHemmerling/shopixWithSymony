<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\DTO\ProductsDataTransferObject;
use App\Entity\Products;
use App\Repository\AttributesRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductsEntityManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly CategoryRepository $categoryRepository, private AttributesRepository $attributesRepository )
    {
    }

    public function create(ProductsDataTransferObject $productDTO): void
    {
        $newProduct = new Products();
        $newProduct->setArticleNumber($productDTO->articleNumber);
        $newProduct->setProductName($productDTO->productName);
        $newProduct->setPrice($productDTO->price);
        $newProduct->setDescription($productDTO->description);
        $categoryData = $this->categoryRepository->findOneOrCreate($productDTO->category);
        $newProduct->setCategory($categoryData);
        foreach ($productDTO->attributes as $attribute) {
            $attributeData = $this->attributesRepository->findOneOrCreate($attribute);
            $newProduct->addAttribute($attributeData);
        }
        $this->entityManager->persist($newProduct);
        $this->entityManager->flush();
    }

    public function save(Products $product, ProductsDataTransferObject $productsDTO): void
    {
        $savedProduct = $product;
        $savedProduct->setArticleNumber($productsDTO->articleNumber);
        $savedProduct->setProductName($productsDTO->productName);
        $savedProduct->setPrice($productsDTO->price);
        $categoryData = $this->categoryRepository->findOneBy(['name' => $productsDTO->category]);
        $savedProduct->setCategory($categoryData);
        $savedProduct->setDescription($productsDTO->description);
        foreach ($productsDTO->attributes as $attribute) {
            $attributeData = $this->attributesRepository->findOneBy(['attribut' => $attribute]);
            $newProduct->addAttribute($attributeData);
        }
        $this->entityManager->persist($savedProduct);
        $this->entityManager->flush();
    }
}