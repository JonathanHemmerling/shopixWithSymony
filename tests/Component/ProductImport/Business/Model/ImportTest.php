<?php

declare(strict_types=1);

namespace App\Tests\Component\ProductImport\Business\Model;

use App\Component\Product\Business\ProductBusinessFascade;
use App\Component\ProductImport\Business\ProductImportBusinessFascade;
use App\Component\ProductImport\DTO\FilePathValueObject;
use App\Component\Productstorage\Business\Model\ProductStorage;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Predis\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;


class ImportTest extends WebTestCase
{

    use InteractsWithMessenger;
    private EntityManagerInterface $entityManager;
    private ProductBusinessFascade $productBusinessFascade;
    private ProductImportBusinessFascade $productImportBusinessFascade;
    private ClientInterface $predisClient;
    private ProductStorage $productStorage;


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->productBusinessFascade = $this->container->get(ProductBusinessFascade::class);
        $this->productImportBusinessFascade = $this->container->get(ProductImportBusinessFascade::class);
        $this->productStorage = $this->container->get(ProductStorage::class);
        $this->predisClient = $this->container->get(Client::class);
    }

    protected function tearDown(): void
    {
        $this->predisClient->flushall();
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
        $this->entityManager->close();
    }

    //Integration Test For The Hole Import Process
    public function testImportToDB()
    {
        $filePath = new FilePathValueObject(__DIR__ . '/../../MOCK_DATA.csv');
        $this->productImportBusinessFascade->import($filePath);
        $products = $this->messenger('product')->queue()->all();
        foreach ($products as $product){
            $productDto = $product->getMessage();
            $this->productBusinessFascade->create($productDto);
        }

        $product1 = $this->productStorage->getProductFromRedis('1');
        $product100 = $this->productStorage->getProductFromRedis('100');
        self::assertSame('Electronics', $product1->category);
        self::assertSame('Seedlings - Buckwheat, Organic', $product100->productName);
    }
}
