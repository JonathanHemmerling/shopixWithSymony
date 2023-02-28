<?php

declare(strict_types=1);

namespace App\Tests\Component\ProductImport\Business;

use App\Component\ProductImport\Business\ProductImportBusinessFascade;
use App\Component\ProductImport\DTO\FilePathValueObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ProductImportBusinessFascadeTest extends WebTestCase
{
    use InteractsWithMessenger;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->productImportBusinessFascade = $this->container->get(ProductImportBusinessFascade::class);
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
        $this->entityManager->close();
    }
    public function testImportToQu()
    {
        $filePath = new FilePathValueObject(__DIR__ . '/../MOCK_DATA.csv');
        $this->productImportBusinessFascade->import($filePath);

        $products = $this->messenger('product')->queue()->all();
        self::assertSame('Veal - Insides, Grains',$products[0]->getMessage()->productName);

        $this->messenger('category')->queue()->assertCount(22);
        $this->messenger('product')->queue()->assertCount(200);
        $this->messenger('attributes')->queue()->assertCount(12);
    }
}
