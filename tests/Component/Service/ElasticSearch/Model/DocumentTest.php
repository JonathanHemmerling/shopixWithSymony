<?php

declare(strict_types=1);

namespace App\Tests\Component\Service\ElasticSearch\Model;

use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Productstorage\Business\Model\ProductStorage;
use App\Component\Productstorage\Mapper\RedisMapper;
use App\Component\Service\ElasticSearch\Model\Document;
use App\Entity\Category;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManager;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Elastica\Client as ElasticaClient;

class DocumentTest extends WebTestCase
{
    private EntityManager $entityManager;
    private ProductsRepository $productsRepository;
    private Document $document;
    private ElasticaClient $elasticaClient;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->productsRepository = $this->container->get(ProductsRepository::class);
        $this->elasticaClient = $this->container->get(ElasticaClient::class);
        $this->document = new Document($this->elasticaClient, $this->productsRepository);
        $this->createProductData();
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
        $this->document->delete();
        $this->entityManager->close();
    }
    public function testIfDocumentUploadWorks()
    {
        $this->document->add();
        $document = $this->document->getAllProducts(4);

        self::assertSame('', $document);
    }
    private function createProductData()
    {
        $data = [
            [
                'category' => '1',
                'articleNumber' => '12345',
                'productName' => 'jeans1',
                'description' => 'description',
                'attributes' => 'test1',
                'price' => 1999,
            ],
            [
                'category' => '1',
                'articleNumber' => '12345',
                'productName' => 'jeans2',
                'description' => 'description',
                'attributes' => 'test1',
                'price' => 1999,
            ],
            [
                'category' => '2',
                'articleNumber' => '12345',
                'productName' => 'jeans1',
                'description' => 'description',
                'attributes' => 'test1',
                'price' => 1999,
            ],
            [
                'category' => '3',
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
