<?php

declare(strict_types=1);

namespace App\Tests\Component\ProductImport\Business\EntityImport;

use App\Component\ProductImport\Business\EntityImport\AttributeImportCollection;
use App\Component\ProductImport\DTO\FilePathValueObject;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class AttributeImportCollectionTest extends WebTestCase
{
    use InteractsWithMessenger;
    private AttributeImportCollection $attributeImportCollection;
    private Reader $reader;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->attributeImportCollection = $this->container->get(AttributeImportCollection::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testIfProductIsDispatched()
    {
        $filePathValueObject = new FilePathValueObject(__DIR__.'/../../MOCK_DATA.csv');
        $file = fopen($filePathValueObject->filePath, 'r');
        $header = fgetcsv($file);
        $csv = Reader::createFromPath($filePathValueObject->filePath, 'r');
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();
        $records = $csv->getRecords();
        $this->attributeImportCollection->import($records);
        $attributes = $this->messenger('attributes')->queue()->all();

        self::assertCount(12, $attributes);
        self::assertSame('Consumer Services', $attributes[0]->getMessage()->attribute);
        self::assertSame('Miscellaneous', $attributes[8]->getMessage()->attribute);

    }
}
