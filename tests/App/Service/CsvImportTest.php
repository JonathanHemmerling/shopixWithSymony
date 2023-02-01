<?php

declare(strict_types=1);

namespace App\Service;

use App\Component\Product\Business\ProductsBusinessFascade;
use App\Component\Product\Persistence\AttributesRepository;
use App\Component\Product\Persistence\ProductsEntityManager;
use App\Component\Product\Persistence\ProductsRepository;
use App\DTO\AttributesDataTransferObject;
use App\DTO\ProductsDataTransferObject;


use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvImportTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private ProductsRepository $productsRepository;
    private AttributesRepository $attributesRepository;
    private ProductsBusinessFascade $productsFascade;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->productsFascade = new ProductsBusinessFascade(new ProductsEntityManager($this->entityManager));
        $this->productsRepository = $this->client->getContainer()->get(ProductsRepository::class);
        $this->attributesRepository = $this->client->getContainer()->get(AttributesRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate('DELETE FROM attributes');
        $connection->executeUpdate('ALTER TABLE attributes AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM products');
        $connection->executeUpdate('ALTER TABLE products AUTO_INCREMENT=0');
        $this->entityManager = null;
    }

    public function testProductCreate(): void
    {
        $import = new CsvImport($this->productsFascade);
        $import->import(__DIR__ . '/MOCK_DATA.csv');

        $product = $this->productsRepository->findOneBy(['productName' => 'Veal - Insides, Grains']);
        self::assertSame('Electronics', $product->getCategory());
        self::assertSame(6989 , $product->getPrice());
        self::assertSame('n/a', $product->getAttr()[0]->getAttributeName());
        self::assertSame('n/a', $product->getAttr()[0]->getAttributeName1());
        self::assertSame('Consumer Services', $product->getAttr()[0]->getAttributeName2());
    }
    public function testProductSave(): void
    {
        $import = new CsvImport($this->productsFascade);
        $import->import(__DIR__ . '/MOCK_DATA.csv');

        $product = $this->productsRepository->findOneBy(['productName' => 'Cherries - Fresh']);
        $attributes = $this->attributesRepository->findOneBy(['id' => $product->getId()]);

        $productsDTO = new ProductsDataTransferObject();
        $attributesDTO = new AttributesDataTransferObject();
        $productsDTO->id = $product->getId();
        $productsDTO->articleNumber = $product->getArticleNumber();
        $productsDTO->productName = 'cherries - fresh';
        $productsDTO->price = $product->getPrice();
        $productsDTO->category = $product->getCategory();
        $productsDTO->description = $product->getDescription();
        $attributesDTO->attributeName = $attributes->getAttributeName();
        $attributesDTO->attributeName1 = $attributes->getAttributeName1();
        $attributesDTO->attributeName2 = $attributes->getAttributeName2();

        $this->productsFascade->save($product, $attributes, $productsDTO, $attributesDTO);

        $product = $this->productsRepository->findOneBy(['id' => 3]);
        self::assertSame('cherries - fresh', $product->getProductName());


    }


}
