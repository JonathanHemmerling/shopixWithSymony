<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\Component\Productstorage\Business\ProductstorageBusinessFascade;
use App\Component\Productstorage\Mapper\RedisMapper;
use App\Component\Productstorage\Persistence\ProductstorageEntityManager;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Products;
use App\Repository\AttributesRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ProductsEntityManager
{
    public function __construct(private ProductstorageBusinessFascade $productstorageBusinessFascade, private RedisMapper $redisMapper, private ProductsRepository $productsRepository, private EntityManagerInterface $entityManager, private CategoryRepository $categoryRepository, private AttributesRepository $attributesRepository )
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
        $this->productstorageBusinessFascade->createRedisEntry($productDTO->productName);
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
            $savedProduct->addAttribute($attributeData);
        }
        $this->entityManager->persist($savedProduct);
        $this->entityManager->flush();
    }
}