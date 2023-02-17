<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\Component\User\Persistence\Repository\UserRepository;
use App\Entity\Category;
use App\Entity\Products;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductRepositoryTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private KernelBrowser $client;

    protected function setUp():void
    {
       parent::setUp();
        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createData();
        $this->createUserData();
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user1@test.de');

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

   public function testAllProductPage():void
    {
        $crawler = $this->client->request('GET', '/product/allProducts/1');
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h2', 'Products');

        $productsList = $crawler->filter('ul.product > li > a');
        self::assertCount(1, $productsList);
        $productInfo = $productsList->getNode(0);
        self::assertSame('jeans1', $productInfo->nodeValue);
    }

    public function testSingleProductPage(): void
    {
        $crawler = $this->client->request('GET', 'product/product/1');
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h2', 'Product');
        $product = $crawler->filter('ul.product > li');
        self::assertCount(3, $product);

        $productInfo = $product->getNode(0);
        self::assertSame('jeans1', $productInfo->nodeValue);
        $productInfo = $product->getNode(1);
        self::assertSame('description', $productInfo->nodeValue);
        $productInfo = $product->getNode(2);
        self::assertSame('1999', $productInfo->nodeValue);
    }

    private function createData()
    {
        $data = [
            [
                'category' => 'test1',
                'productName' => 'jeans1',
                'displayName' => 'Jeans 1',
                'description' => 'description',
                'price' => 1999,
            ],
            [
                'category' => 'test2',
                'productName' => 'jeans2',
                'displayName' => 'Jeans 2',
                'description' => 'description',
                'price' => 1997,
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
                'email' => 'user1@test.de',
                'role' => 'ROLE_USER',
                'password' => '123456789'
            ],
            [
                'email' => 'user2@test.de',
                'role' => 'ROLE_ADMIN',
                'password' => '987654321'
            ],
        ];
        foreach ($data as $userList){
            $user = new User;
            $user->setEmail($userList['email']);
            $user->setRoles([$userList['role']]);
            $user->setPassword($userList['password']);
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
    }
}
