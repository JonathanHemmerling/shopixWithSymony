<?php

declare(strict_types=1);

namespace App\Component\ProductImport\Business\Model;

use App\Component\Attributes\Business\AttributesBusinessFascade;
use App\Component\Category\Business\CategoryBusinessFascade;
use App\Component\Product\Business\ProductBusinessFascade;
use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Attributes\Persistence\AttributesEntityManager;
use App\Component\Product\Persistence\ProductsEntityManager;
use App\Component\ProductImport\Business\ProductImportBusinessFascade;
use App\Component\ProductImport\DTO\FilePathValueObject;
use App\Mapper\AttributesMapper;
use App\Mapper\CategoryMapper;
use App\Message\MyMessage\AttributeMessageHandler;
use App\Message\MyMessage\CategoryMessageHandler;
use App\Message\MyMessage\MyMessageHandler;
use App\Repository\ProductsRepository;
use App\Tests\Message\MyMessage\MessageBusSpy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Snc\RedisBundle\Client\Phpredis\Client;
use Symfony\Component\Messenger\MessageBus;

class ImportTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private AttributesMapper $attributesMapper;
    private AttributesBusinessFascade $attributesBusinessFascade;
    private CategoryMapper $categoryMapper;
    private CategoryBusinessFascade $categoryBusinessFascade;
    private ProductsMapper $productsMapper;
    private ProductBusinessFascade $productBusinessFascade;
    private ProductsRepository $productsRepository;
    private ProductsEntityManager $productsEntityManager;
    private MessageBusSpy $messageBusSpy;
    private ProductImportBusinessFascade $productImportBusinessFascade;
    private AttributeMessageHandler $attributeMessageHandler;
    private CategoryMessageHandler $categoryMessageHandler;
    private MyMessageHandler $messageHandler;
    private $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->container = $this->client->getContainer();
        $this->attributesMapper = new AttributesMapper();
        $attributesEntityManager = new AttributesEntityManager($this->entityManager);
        $this->attributesBusinessFascade = new AttributesBusinessFascade($attributesEntityManager);
        $this->categoryMapper = new CategoryMapper();
        $this->categoryBusinessFascade = $this->container->get(CategoryBusinessFascade::class);
        $this->productsMapper = new ProductsMapper();
        $this->productBusinessFascade = $this->container->get(ProductBusinessFascade::class);
        $this->productsRepository = $this->container->get(ProductsRepository::class);
        $this->productsEntityManager = $this->container->get(ProductsEntityManager::class);
        $this->messageBusSpy = new MessageBusSpy();

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

    public function test()
    {
        $filePath = new FilePathValueObject(__DIR__ . '/../../MOCK_DATA.csv');

        $this->productImportBusinessFascade->import($filePath);

        $product = $this->productsRepository->findOneBy(['id'=>1]);
        self::assertSame('', $product->getProductName());
    }
    /*  public function testCSVError(): void
      {
          $filePath = $this->getMockBuilder(FilePathValueObject::class)
              ->setConstructorArgs([__DIR__ . 'MOCK_DATA.csv'])
              ->getMock();
          $filePath->expects(InvalidArgumentException::class);
      }*/
    /*
    public function testImport(): void
    {
        $filePath = new FilePathValueObject(__DIR__ . '/../../MOCK_DATA.csv');
        $productImportFascade = new ProductImportBusinessFascade($this->import);
        $productImportFascade->import($filePath);
        $info = $this->messageBusSpy->info;

        self::assertInstanceOf(AttributesDataTransferObject::class, $info[0]);
        self::assertInstanceOf(CategoryDataTransferObject::class, $info[12]);
        self::assertInstanceOf(ProductsDataTransferObject::class, $info[34]);
        self::assertSame('Consumer Services',$info[0]->attribute);
        self::assertSame($info[0]->attribute ,$info[34]->attributes['Consumer Services']);
        self::assertSame('Health Care', $info[5]->attribute);
    }

    public function testProductSave(): void
    {
        $this->createData();
        $product = $this->productsRepository->findOneBy(['productName' => 'productName']);

        $productsDTO = new ProductsDataTransferObject();
        $productsDTO->id = $product->getId();
        $productsDTO->articleNumber = $product->getArticleNumber();
        $productsDTO->productName = 'cherries - fresh';
        $productsDTO->price = $product->getPrice();
        $productsDTO->category = $product->getCategory()->getName();
        $productsDTO->description = $product->getDescription();
        $productsDTO->attribute = $product->getAttribute()->getValues()[0]->getAttribut();

        $this->productBusinessFascade->save($product, $productsDTO);

        $product = $this->productsRepository->findOneBy(['id' => 1]);
        self::assertSame('cherries - fresh', $product->getProductName());
    }

    private function createData():void
    {
        $data = new ProductsDataTransferObject(
            ['attributes'],
            'category',
            'articleNumber',
            'productName',
            0,
            'description');

        $this->productsEntityManager->create($data);
    }
    */

}
