<?php

declare(strict_types=1);

namespace App\Tests\Component\ProductImport\Business\EntityImport;

use App\Component\ProductImport\Business\EntityImport\ProductImportCollection;
use App\Component\ProductImport\DTO\FilePathValueObject;
use App\Component\Productstorage\Persistence\ProductstorageRepository;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;


class ProductImportCollectionTest extends WebTestCase
{
    use InteractsWithMessenger;
    private ProductImportCollection $productImportCollection;
    private Reader $reader;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->productImportCollection = $this->container->get(ProductImportCollection::class);
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
        $this->productImportCollection->import($records);
        $products = $this->messenger('product')->queue()->all();

        self::assertSame('Electronics',$products[0]->getMessage()->category);
        self::assertSame(['Consumer Services' => 'Consumer Services'], $products[0]->getMessage()->attributes);
        self::assertSame(6989, $products[0]->getMessage()->price);
        self::assertNotSame('6989', $products[0]->getMessage()->price);
        self::assertSame('Veal - Insides, Grains', $products[0]->getMessage()->productName);
        self::assertSame('185540242-4', $products[0]->getMessage()->articleNumber);
        self::assertSame('in quis justo maecenas', $products[0]->getMessage()->description);


    }
}
