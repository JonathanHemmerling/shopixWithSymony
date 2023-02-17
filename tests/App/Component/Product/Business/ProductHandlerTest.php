<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Mapper\ProductsMapper;
use App\Component\Product\Persistence\ProductsEntityManager;
use App\DTO\ProductsDataTransferObject;
use App\Repository\AttributesRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManager;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductHandlerTest extends WebTestCase
{
    private Client $redisClient;
    private EntityManager $entityManager;
    private ProductsRepository $productsRepository;
    private ProductsEntityManager $productsEntityManager;
    private ProductsMapper $productsMapper;
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
        $this->redisClient = new Client();

        $this->createProduct();
    }

    public function testJsonEncode():void
    {
        $productHandler = new ProductHandler($this->productsMapper, $this->productsRepository, $this->redisClient);
        $productHandler->sendProductAsJsonToRedis(2);
        $product = $productHandler->getProductFromRedis(2);
        self::assertSame('', $product);

    }

    public function createProduct():void
    {
        $data = new ProductsDataTransferObject(
            ['test1'],
            'test1',
            'test1',
            'test1',
            1,
            'test1');

        $this->productsEntityManager->create($data);
    }
}
