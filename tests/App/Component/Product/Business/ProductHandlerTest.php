<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Product\Persistence\ProductsEntityManager;
use App\Component\ProductImport\Business\ProductImportBusinessFascade;
use App\Component\ProductImport\Communication\Command\ImportCommand;
use App\Component\ProductImport\DTO\FilePathValueObject;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Products;
use App\Message\MyMessage\AttributeMessageHandler;
use App\Message\MyMessage\MyMessageHandler;
use App\Repository\AttributesRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManager;
use Predis\Client as PredisClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductHandlerTest extends WebTestCase
{
    private PredisClient $redisClient;
    private EntityManager $entityManager;
    private ProductsRepository $productsRepository;
    private ProductsEntityManager $productsEntityManager;
    private ProductsMapper $productsMapper;
    private ProductImportBusinessFascade $productImportBusinessFascade;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $container = $this->client->getContainer();
        $this->productsRepository = $container->get(ProductsRepository::class);
        $categoryRepository = $container->get(CategoryRepository::class);
        $attributesRepository = $container->get(AttributesRepository::class);
        $this->productsEntityManager = new ProductsEntityManager($this->entityManager, $categoryRepository, $attributesRepository);
        $this->productsMapper = $container->get(ProductsMapper::class);
        $this->productImportBusinessFascade = $container->get(ProductImportBusinessFascade::class);
        $this->redisClient = new PredisClient();

        $this->createProduct();
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

    public function testJsonEncode():void
    {
        $filePath = new FilePathValueObject(__DIR__.'/../../ProductImport/MOCK_DATA.csv');
        $this->productImportBusinessFascade->import($filePath);
        $command = new ImportCommand($this->productImportBusinessFascade);
        $command->addArgument(__DIR__.'/../../ProductImport/MOCK_DATA.csv');
        $productHandler = new ProductHandler($this->productsMapper, $this->productsRepository, $this->redisClient);
        $productHandler->sendProductAsJsonToRedis(1);
        $product = $productHandler->getProductFromRedis(1);
        self::assertSame('testProductName', $product->productName);
    }

    public function createProduct():void
    {
        $product = new Products();
        $dto = $this->productsMapper->mapToProductsDto(['attributes' => ['attr1', 'attr2'], 'category' => 'test3Category', 'articleNumber' => 'testArticleNumber', 'productName' => 'testProductName', 'price' => 0, 'description' => 'testDescription']);

        $this->productsEntityManager->create($dto);
    }
}
