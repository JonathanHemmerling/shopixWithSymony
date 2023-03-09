<?php

declare(strict_types=1);

namespace App\Tests\Component\Categorystorage\Business\Model;

use App\Component\Category\Mapper\CategoryMapper;
use App\Component\Categorystorage\Business\Model\CategoryStorage;
use App\Entity\Category;
use App\Entity\Products;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class CategorystorageTest extends WebTestCase
{

    use InteractsWithMessenger;

    private EntityManagerInterface $entityManager;
    private CategoryStorage $categorystorage;
    private CategoryMapper $categoryMapper;
    private ClientInterface $redisClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $categoryRepository = $this->container->get(CategoryRepository::class);
        $messageBus = $this->container->get(MessageBusInterface::class);
        $this->categoryMapper = $this->container->get(CategoryMapper::class);
        $this->redisClient = $this->container->get(Client::class);
        $productsRepository = $this->container->get(ProductsRepository::class);
        $this->categorystorage = new CategoryStorage(
            $this->categoryMapper,
            $categoryRepository,
            $this->redisClient,
            $messageBus,
            $productsRepository,

        );
        $this->createProductData();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->redisClient->flushall();
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate('DELETE FROM products');
        $connection->executeUpdate('ALTER TABLE products AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM attributes');
        $connection->executeUpdate('ALTER TABLE attributes AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM products_attributes');
        $connection->executeUpdate('ALTER TABLE products_attributes AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM category');
        $connection->executeUpdate('ALTER TABLE category AUTO_INCREMENT=0');
        $this->entityManager->close();
    }
    public function testDispatchAsDTO(): void
    {
        $this->categorystorage->sendCategoryAsDtoToRabbitMQ(1);
        $category = $this->messenger('category')->queue()->all();

        self::assertSame('test1', $category[0]->getMessage()->name);
    }
    public function testSendJsonToRedis(): void
    {
        $category = $this->categoryMapper->mapToRedisCategoryDto(['name'=>'testName', 'id' => 1, 'products' => ['testProduct1', 'testProduct2']]);

        $this->categorystorage->sendCategoryAsJsonToRedis($category);

        $value = $this->redisClient->get('"Category:1"');
        self::assertSame('{"name":"testName","products":["testProduct1","testProduct2"]}', $value);
    }
    public function testGetProductsFromRedis()
    {
        $key1 = json_encode('Category:1');
        $value1 = json_encode(['name' => 'testName', 'products' => ['testproduct1', 'testproduct2']]);
        $key2 = json_encode('Category:2');
        $value2 = json_encode(['name' => 'testName2', 'products' => ['testproduct1', 'testproduct2']]);

        $this->redisClient->set($key2, $value2);
        $this->redisClient->set($key1, $value1);

        $results = $this->categorystorage->getAllCategorysFromRedis();
        $category = $this->categorystorage->getCategoryFromRedis('1');
        self::assertSame('testName', $category->name);
    }

    private function createProductData()
    {
        $data = [
            [
                'category' => 'test1',
                'articleNumber' => '12345',
                'productName' => 'jeans1',
                'description' => 'description',
                'attributes' => 'test1',
                'price' => 1999,
            ],
            [
                'category' => 'test1',
                'articleNumber' => '12345',
                'productName' => 'jeans2',
                'description' => 'description',
                'attributes' => 'test1',
                'price' => 1999,
            ],
            [
                'category' => 'test2',
                'articleNumber' => '12345',
                'productName' => 'shirt1',
                'description' => 'description',
                'attributes' => 'test1',
                'price' => 1999,
            ],
        ];

        foreach ($data as $productData) {
            $product = new Products();
            $category = new Category();
            $category->setName($productData['category']);
            $product->setCategory($category);
            $product->setArticleNumber($productData['articleNumber']);
            $product->setProductName($productData['productName']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();
    }
}
