<?php

declare(strict_types=1);

namespace App\Tests\Component\Productstorage\Business;

use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Productstorage\Business\Model\ProductStorage;
use App\Component\Productstorage\Business\ProductstorageBusinessFascade;
use App\Component\Productstorage\Mapper\RedisMapper;
use App\Component\Productstorage\Persistence\ProductstorageEntityManager;
use App\Entity\Category;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Predis\Client;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductstorageBusinessFascadeTest extends WebTestCase
{
    private RedisMapper $redisMapper;
    private ProductsRepository $productsRepository;
    private ProductStorage $productStorage;
    private ProductstorageBusinessFascade $productstorageBusinessFascade;
    private ClientInterface $redisClient;
    private ProductsMapper $productsMapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->redisMapper = $this->container->get(RedisMapper::class);
        $this->productsMapper = $this->container->get(ProductsMapper::class);
        $this->productsRepository = $this->container->get(ProductsRepository::class);
        $this->redisClient = $this->getMockBuilder(Client::class)->addMethods(['set', 'get'])->getMock();
        $this->productStorage = new ProductStorage($this->productsMapper,$this->redisMapper, $this->productsRepository, $this->redisClient, $this->container->get(MessageBusInterface::class));
        $this->productstorageBusinessFascade = new ProductstorageBusinessFascade(
            $this->redisMapper,
            $this->productStorage,
            $this->productsRepository,
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

    public function testCreateRedisEntry(): void
    {
        $this->redisClient->expects($this->once())->method('set')->with('"Product:1"', '{"category":"test1","articleNumber":"12345","productName":"value","price":1999,"description":"description","attributes":[]}');
        $this->redisClient->expects($this->once())->method('get')->with('1')->willReturn('{"category":"test1","articleNumber":"12345","productName":"value","price":1999,"description":"description","attributes":[]}');

        $this->productstorageBusinessFascade->createRedisEntry('value');

        $value = $this->redisClient->get('1');

        self::assertSame(
            '{"category":"test1","articleNumber":"12345","productName":"value","price":1999,"description":"description","attributes":[]}',
            $value
        );
    }

    private function createProductData()
    {
        $data = [
            [
                'category' => 'test1',
                'articleNumber' => '12345',
                'productName' => 'value',
                'description' => 'description',
                'attribute' => 'test1',
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
