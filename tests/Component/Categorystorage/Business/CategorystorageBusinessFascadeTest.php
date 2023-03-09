<?php

declare(strict_types=1);

namespace App\Tests\Component\Categorystorage\Business;

use App\Component\Category\Mapper\CategoryMapper;
use App\Component\Categorystorage\Business\CategorystorageBusinessFascade;
use App\Component\Categorystorage\Business\Model\CategoryStorage;
use App\Component\Productstorage\Business\Model\ProductStorage;
use App\Component\Service\ElasticSearch\Factory\ClientFactory;
use App\Entity\Category;
use App\Entity\Products;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManager;
use Elastica\Document;
use Predis\Client;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

class CategorystorageBusinessFascadeTest extends WebTestCase
{
    private EntityManager $entityManager;
    private CategoryMapper $categoryMapper;
    private CategoryStorage $categoryStorage;
    private CategoryRepository $categoryRepository;
    private CategorystorageBusinessFascade $categorystorageBusinessFascade;
    private ClientInterface $redisClient;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->categoryMapper = $this->container->get(CategoryMapper::class);
        $this->categoryRepository = $this->container->get(CategoryRepository::class);
        $this->redisClient = $this->container->get(Client::class);
        $this->categoryStorage = new CategoryStorage($this->categoryMapper, $this->categoryRepository, $this->redisClient, $this->container->get(MessageBusInterface::class), $this->container->get(ProductsRepository::class));
        $this->categorystorageBusinessFascade = new CategorystorageBusinessFascade(
            $this->categoryMapper,
            $this->categoryStorage,
            $this->categoryRepository,
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

        $this->categorystorageBusinessFascade->createRedisEntry(1);

        $value = $this->redisClient->get('"Category:1"');
        self::assertSame('{"name":"test1","products":[value]}', $value);
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
