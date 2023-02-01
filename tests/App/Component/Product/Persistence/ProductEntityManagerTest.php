<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\DTO\ProductDataTransferObject;
use App\Entity\Product;
use App\Entity\User;
use App\Component\User\Persistence\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ProductEntityManagerTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private ProductRepository $productRepository;
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createProductData();
        $this->productRepository = $this->client->getContainer()->get(ProductRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $connection->query('TRUNCATE product');
        $this->entityManager = null;
    }

    public function testProductCreate(): void
    {
        $productDto = new ProductDataTransferObject();
        $productDto->mainId = 1;
        $productDto->productName = 'test';
        $productDto->displayName = 'test';
        $productDto->description = 'test';
        $productDto->price = '1';
        $productEntityManager = new ProductEntityManager($this->entityManager);
        $productEntityManager->create($productDto);

        $product = $this->productRepository->findOneBy(['productName' => 'test']);
        $this->assertSame('test', $product->getProductName());
        $this->assertSame('1', $product->getPrice());
    }

    public function testProductSave():void
    {
        $product = $this->productRepository->findBy(['id' => 1]);
        $newDTO = new ProductDataTransferObject();
        $newDTO->mainId = $product[0]->getMainId();
        $newDTO->productName = $product[0]->getProductName();
        $newDTO->displayName = 'test';
        $newDTO->description = 'test';
        $newDTO->price = '1';

        $productEntityManager = new ProductEntityManager($this->entityManager);
        $productEntityManager->save($product[0], $newDTO);

        $product = $this->productRepository->findOneBy(['id' => 1]);
        self::assertSame('jeans1', $product->getProductName());
        self::assertSame('test', $product->getDisplayName());
    }


    private function createProductData()
    {
        $data = [
            [
                'mainId' => 1,
                'productName' => 'jeans1',
                'displayName' => 'Jeans 1',
                'description' => 'description',
                'price' => '19,99',
            ],
            [
                'mainId' => 2,
                'productName' => 'jeans2',
                'displayName' => 'Jeans 2',
                'description' => 'description',
                'price' => '19,97',
            ],
        ];

        foreach ($data as $productData) {
            $product = new Product();

            $product->setMainId($productData['mainId']);
            $product->setProductName($productData['productName']);
            $product->setDisplayName($productData['displayName']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();
    }

}
