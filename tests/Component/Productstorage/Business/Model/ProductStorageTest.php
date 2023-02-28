<?php

declare(strict_types=1);

namespace App\Tests\Component\Productstorage\Business\Model;

use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Productstorage\Business\Model\ProductStorage;
use App\Component\Productstorage\Mapper\RedisMapper;
use App\Entity\Category;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ProductStorageTest extends WebTestCase
{

    use InteractsWithMessenger;

    private EntityManagerInterface $entityManager;
    private ProductStorage $productStorage;
    private ProductsMapper $productsMapper;
    private RedisMapper $redisMapper;
    private ClientInterface $redisClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $productsRepository = $this->container->get(ProductsRepository::class);
        $messageBus = $this->container->get(MessageBusInterface::class);
        $this->redisMapper = new RedisMapper();
        $this->productsMapper = $this->container->get(ProductsMapper::class);
        $this->redisClient = $this->container->get(Client::class);
        $this->productStorage = new ProductStorage(
            $this->productsMapper,
            $this->redisMapper,
            $productsRepository,
            $this->redisClient,
            $messageBus
        );
        $this->createProductData();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->redisClient->del(6);
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
        $this->productStorage->sendProductAsDtoToRabbitMQ(1);
        $product = $this->messenger('productredis')->queue()->all();

        self::assertSame('jeans1', $product[0]->getMessage()->productName);
    }
    public function testSendJsonToRedis(): void
    {
        $category = new Category();
        $category->setName('test');
        $product = $this->redisMapper->mapToRedisDto([
            'id' => 6,
            'category' => $category,
            'articleNumber' => '12345',
            'productName' => 'testRedis',
            'description' => 'testRedis',
            'attributes' => ['testRedis'],
            'price' => 199,
        ],
        );
        $this->productStorage->sendProductAsJsonToRedis($product);

        $value = $this->redisClient->get('"Product:6"');
        self::assertSame(
            '{"category":"test","articleNumber":"12345","productName":"testRedis","price":199,"description":"testRedis","attributes":["testRedis"]}',
            $value
        );
    }
    public function testGetProductsFromRedis()
    {
        $key = json_encode('Product:1');
        $value = json_encode(['attributes' => ['test'], 'category' => 'test', 'articleNumber' => 'test','productName' => 'test','price' => 10,'description' => 'test']);
        $this->redisClient->set($key, $value);
        $product = $this->productStorage->getProductFromRedis('1');
        self::assertSame('test', $product->productName);
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
