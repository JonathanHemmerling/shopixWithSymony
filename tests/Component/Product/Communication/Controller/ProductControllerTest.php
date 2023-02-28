<?php

declare(strict_types=1);

namespace App\Tests\Component\Product\Communication\Controller;

use App\Component\User\Persistence\Repository\UserRepository;
use App\Entity\Category;
use App\Entity\Products;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
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
        $this->createMainmenuData();
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test2@test.de');

        $this->client->loginUser($testUser);
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
        $connection->executeUpdate('DELETE FROM user');
        $connection->executeUpdate('ALTER TABLE user AUTO_INCREMENT=0');
        $this->entityManager = null;
    }

    public function testCategoryOverview(): void
    {
        $crawler = $this->client->request('GET', '/entry');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $list = $crawler->filter('li');
        $button = $crawler->filter('form > input');
        self::assertCount(4, $links);
        self::assertCount(4, $list);
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


    private function createMainMenuData(): void
    {
        $data = [
            [
                'mainName' => 'test3',
            ],
            [
                'mainName' => 'test4',
            ],
        ];

        foreach ($data as $mainMenuData) {
            $mainCategory = new Category();
            $mainCategory->setName($mainMenuData['mainName']);
            $this->entityManager->persist($mainCategory);
        }
        $this->entityManager->flush();
    }

    private function createProductData()
    {
        $data = [
            [
                'category' => 'test1',
                'productName' => 'jeans1',
                'displayName' => 'Jeans 1',
                'description' => 'description',
                'price' => 1999,
                'attribute' => 'test1',
                'attribute2' => 'test2',
                'attribute3' => 'test3',
            ],
            [
                'category' => 'test2',
                'productName' => 'jeans2',
                'displayName' => 'Jeans 2',
                'description' => 'description',
                'price' => 1997,
                'attribute' => 'test1',
                'attribute2' => 'test2',
                'attribute3' => 'test3',
            ],
        ];

        foreach ($data as $productData) {
            $product = new Products();
            $category = new Category();
            $category->setName($productData['category']);
            $product->setCategory($category);
            $product->setProductName($productData['productName']);
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
