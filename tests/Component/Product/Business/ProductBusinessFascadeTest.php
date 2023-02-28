<?php

declare(strict_types=1);

namespace App\Tests\Component\Product\Business;

use App\Component\Attributes\Business\AttributesBusinessFascade;
use App\Component\Attributes\Mapper\AttributesMapper;
use App\Component\Attributes\Persistence\AttributesEntityManager;
use App\Component\Product\Business\ProductBusinessFascade;
use App\Component\Product\Persistence\ProductsEntityManager;
use App\Component\Productstorage\Business\ProductstorageBusinessFascade;
use App\Component\Productstorage\Mapper\RedisMapper;
use App\Component\Productstorage\Persistence\ProductstorageEntityManager;
use App\DTO\ProductDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Products;
use App\Repository\AttributesRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductBusinessFascadeTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private ProductsRepository $productsRepository;
    private ProductBusinessFascade $productsFascade;
    private CategoryRepository $categoryRepository;
    private AttributesRepository $attributesRepository;
    private AttributesBusinessFascade $attributeFascade;
    private RedisMapper $redisMapper;
    private ProductstorageBusinessFascade $productstorageBusinessFascade;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->categoryRepository = $this->client->getContainer()->get(CategoryRepository::class);
        $this->attributesRepository = $this->client->getContainer()->get(AttributesRepository::class);
        $this->productsRepository = $this->client->getContainer()->get(ProductsRepository::class);
        $this->redisMapper = $this->client->getContainer()->get(RedisMapper::class);
        $this->productstorageBusinessFascade = $this->client->getContainer()->get(ProductstorageBusinessFascade::class);
        $this->attributeFascade = new AttributesBusinessFascade(new AttributesEntityManager($this->entityManager));
        $this->productsFascade = new ProductBusinessFascade(
            new ProductsEntityManager($this->productstorageBusinessFascade, $this->redisMapper, $this->productsRepository, $this->entityManager, $this->categoryRepository, $this->attributesRepository)
        );
        $this->createProductData();
        $this->createAttributes();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate('DELETE FROM products');
        $connection->executeUpdate('ALTER TABLE products AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM attributes');
        $connection->executeUpdate('ALTER TABLE attributes AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM products_attributes');
        $connection->executeUpdate('ALTER TABLE products_attributes AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM category');
        $connection->executeUpdate('ALTER TABLE category AUTO_INCREMENT=0');
        $this->entityManager = null;
    }

    public function testProductCreate(): void
    {
        $attributesArray = ['Technology', 'Consumer Service', 'Transportation'];
        $productsDTO = new ProductsDataTransferObject($attributesArray, 'test1', 'test', 'test', 1, 'test');

        $this->productsFascade->create($productsDTO);

        $products = $this->productsRepository->findOneBy(['productName' => 'test']);
        self::assertSame('test', $products->getProductName());
        self::assertSame(1, $products->getPrice());
    }

   public function testProductSave(): void
    {
        $products = $this->productsRepository->findOneBy(['id' => 1]);
        $categoryArray = ['Technology', 'Consumer Service', 'Transportation'];
        $category = new Category();
        $category->setName('test');
        $productsDTO = new ProductsDataTransferObject(
            $categoryArray,
            $products->getCategory()->getName(),
            'test',
            'Test',
            0,
            'Test'
        );

        $this->productsFascade->save($products, $productsDTO);

        $products = $this->productsRepository->findOneBy(['id' => 1]);
        self::assertSame('Test', $products->getProductName());
        self::assertSame('Test', $products->getDescription());
        self::assertSame(0, $products->getPrice());
    }


    private function createAttributes()
    {
        $data = ['Technology', 'Consumer Service', 'Transportation'];

        foreach ($data as $attribute) {
            $attributesMapper = new AttributesMapper();
            $dto = $attributesMapper->mapToAttributesDto($attribute);
            $this->attributeFascade->create($dto);
        }
    }

    private function createProductData()
    {
        $data = [
            [
                'category' => 'test1',
                'productName' => 'jeans1',
                'displayName' => 'Jeans 1',
                'description' => 'description',
                'price' => 1999,
            ],
            [
                'category' => 'test2',
                'productName' => 'jeans2',
                'displayName' => 'Jeans 2',
                'description' => 'description',
                'price' => 1997,
            ],
        ];

        foreach ($data as $productsData) {
            $products = new Products();
            $category = new Category();
            $category->setName($productsData['category']);
            $products->setCategory($category);
            $products->setProductName($productsData['productName']);
            $products->setDescription($productsData['description']);
            $products->setPrice($productsData['price']);
            $this->entityManager->persist($products);
        }
        $this->entityManager->flush();
    }

}
