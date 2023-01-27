<?php

declare(strict_types=1);

namespace App\Controller;

use App\Component\Product\Persistence\ProductRepository;
use App\Entity\MainCategorys;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    protected function setUp():void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createProductData();
        $this->createUserData();
        $this->createMainmenuData();
        $this->productRepository = $this->client->getContainer()->get(ProductRepository::class);
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test2@test.de');

        $this->client->loginUser($testUser);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $connection->query('TRUNCATE main_categorys');
        $connection->query('TRUNCATE product');
        $this->entityManager = null;
    }

    public function testCategoryOverview(): void
    {
        $crawler = $this->client->request('GET', '/entry');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $list = $crawler->filter('li');
        $button = $crawler->filter('form > input');
        self::assertCount(2, $links);
        self::assertCount(2, $list);
        self::assertCount(1, $button);
        self::assertSelectorTextContains('h2', 'Productcategorys');
    }
    public function testProductsOverview(): void
    {
        $crawler = $this->client->request('GET', '/product/allProducts/1');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $list = $crawler->filter('li');
        self::assertCount(2, $links);
        self::assertCount(1, $list);
        self::assertSelectorTextContains('h2', 'Products');
    }
    public function testSingleProductOverview(): void
    {
        $crawler = $this->client->request('GET', '/product/product/2');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $list = $crawler->filter('li');
        self::assertCount(2, $links);
        self::assertCount(3, $list);
        self::assertSelectorTextContains('h2', 'Product');
    }


    private function createMainmenuData(): void
    {
        $data = [
            [
                'mainCategoryName' => 'jeans',
                'displayName' => 'Jeans',
            ],
            [
                'mainCategoryName' => 'pullover',
                'displayName' => 'Pullover',
            ]
        ];

        foreach ($data as $productData){
            $product = new MainCategorys();
            $product->setMainCategoryName($productData['mainCategoryName']);
            $product->setDisplayName($productData['displayName']);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();
    }

    private function createProductData(): void
    {
        $data = [
            [
                'mainId' => 1,
                'productName' => 'jeans1',
                'displayName' => 'Jeans 1',
                'description' => 'description',
                'price' => '19,99'
            ],
            [
                'mainId' => 2,
                'productName' => 'jeans2',
                'displayName' => 'Jeans 2',
                'description' => 'description',
                'price' => '19,97'
            ]
        ];

        foreach ($data as $productData){
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

    private function createUserData(): void
    {
        $data = [
            [
                'email' => 'admin@test.de',
                'role' => 'ROLE_ADMIN',
                'password' => 'password'
            ],
            [
                'email' => 'test2@test.de',
                'role' => 'ROLE_USER',
                'password' => 'password'
            ]
        ];

        foreach ($data as $userData){
            $user = new User();

            $user->setEmail($userData['email']);
            $user->setPassword($userData['password']);
            $user->setRoles([$userData['role']]);
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
    }
}
