<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\ProductEntityManager;
use App\Component\Product\Persistence\ProductRepository;
use App\DTO\ProductsDataTransferObject;
use App\Entity\Product;
use App\Entity\User;
use App\Model\Mapper\ProductsMapper;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductBusinessFascadeTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createProductData();
        $this->createUserData();
        $this->productRepository = $this->client->getContainer()->get(ProductRepository::class);
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@test.de');

        $this->client->loginUser($testUser);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $connection->query('TRUNCATE product');
        $this->entityManager = null;
    }

    public function testProductCreate(): void
    {
        $productDto = new ProductsDataTransferObject(null, 1, 'test', 'test', 'test','test');
        $fascade = new ProductBusinessFascade(new ProductEntityManager($this->entityManager));

        $fascade->create($productDto);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(Product::class)->findOneBy(['productName' => 'test']);
        $this->assertNotNull($product);
    }

    public function testProductSave():void
    {
        $product = $this->client->getContainer()->get(ProductRepository::class)->findBy(['id' => 1]);
        $productMapper = new ProductsMapper();
        $productDto = $productMapper->mapToProductsDto($product[0]);
        $newDTO = new ProductsDataTransferObject($productDto->productId, $productDto->mainId, $productDto->displayName, 'jeans6', $productDto->description, $productDto->price);
        $fascade = new ProductBusinessFascade(new ProductEntityManager($this->entityManager));
        $fascade->save($newDTO);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(Product::class)->findOneBy(['productName' => 'jeans6']);
        self::assertNotNull($product);
    }


    private function createProductData()
    {
        $data = [
            [
                'mainId' => 1,
                'productName' => 'jeans1',
                'displayName' => 'Jeans 1',
                'description' => 'description',
                'price' => '19,99',
            ],
            [
                'mainId' => 2,
                'productName' => 'jeans2',
                'displayName' => 'Jeans 2',
                'description' => 'description',
                'price' => '19,97',
            ],
        ];

        foreach ($data as $productData) {
            $product = new Product();

            $product->setMainId($productData['mainId']);
            $product->setProductName($productData['productName']);
            $product->setDisplayName($productData['displayName']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();
    }

    private function createUserData()
    {
        $data = [
            [
                'email' => 'admin@test.de',
                'role' => 'ROLE_ADMIN',
                'password' => 'password',
            ],
            [
                'email' => 'test2@test.de',
                'role' => 'ROLE_USER',
                'password' => 'password',
            ],
        ];

        foreach ($data as $userData) {
            $user = new User();

            $user->setEmail($userData['email']);
            $user->setPassword($userData['password']);
            $user->setRoles([$userData['role']]);
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
    }

}
