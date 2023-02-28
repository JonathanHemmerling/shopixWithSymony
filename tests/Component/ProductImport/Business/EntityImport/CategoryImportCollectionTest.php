<?php

declare(strict_types=1);

namespace App\Tests\Component\ProductImport\Business\EntityImport;

use App\Component\ProductImport\Business\EntityImport\CategoryImportCollection;
use App\Component\ProductImport\DTO\FilePathValueObject;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class CategoryImportCollectionTest extends WebTestCase
{
    use InteractsWithMessenger;
    private CategoryImportCollection $categoryImportCollection;
    private Reader $reader;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->categoryImportCollection = $this->container->get(CategoryImportCollection::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIfProductIsDispatched()
    {
        $filePathValueObject = new FilePathValueObject(__DIR__ . '/../../MOCK_DATA.csv');
        $file = fopen($filePathValueObject->filePath, 'r');
        $header = fgetcsv($file);
        $csv = Reader::createFromPath($filePathValueObject->filePath, 'r');
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();
        $records = $csv->getRecords();
        $this->categoryImportCollection->import($records);
        $products = $this->messenger('category')->queue()->all();

        self::assertCount(22, $products);
        self::assertSame('Electronics', $products[0]->getMessage()->name);
        self::assertSame('Sports', $products[20]->getMessage()->name);
    }

}
