<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Admin;
use App\Entity\Product;
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
        $connection->query('TRUNCATE product');
        $connection->query('TRUNCATE user');
        $this->entityManager = null;
    }

   public function testAllProductPage():void
    {
        $crawler = $this->client->request('GET', '/product/allProducts/1');
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h2', 'Products');

        $productsList = $crawler->filter('ul.product > li > a');
        self::assertCount(2, $productsList);
        $productInfo = $productsList->getNode(0);
        self::assertSame('Jeans 1', $productInfo->nodeValue);
        $productInfo = $productsList->getNode(1);
        self::assertSame('Jeans 2', $productInfo->nodeValue);
    }

    public function testSingleProductPage(): void
    {
        $this->createData();
        $crawler = $this->client->request('GET', 'product/product/1');
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h2', 'Product');
        $product = $crawler->filter('ul.product > li');
        self::assertCount(3, $product);

        $productInfo = $product->getNode(0);
        self::assertSame('Jeans 1', $productInfo->nodeValue);
        $productInfo = $product->getNode(1);
        self::assertSame('First Jeans', $productInfo->nodeValue);
        $productInfo = $product->getNode(2);
        self::assertSame('11', $productInfo->nodeValue);
    }

    private function createData(): void
    {
        $data = [
            [
                'mainId' => 1,
                'productName' => 'jeans1',
                'displayName' => 'Jeans 1',
                'description' => 'First Jeans',
                'price' => '11',
            ],
            [
                'mainId' => 1,
                'productName' => 'jeans2',
                'displayName' => 'Jeans 2',
                'description' => 'Sec Jeans',
                'price' => '12',
            ],
            [
                'mainId' => 2,
                'productName' => 'pullover1',
                'displayName' => 'Pullover 1',
                'description' => 'First Pullover',
                'price' => '12',
            ],
            [
                'mainId' => 2,
                'productName' => 'pullover2',
                'displayName' => 'Pullover 2',
                'description' => 'Sec Pullover',
                'price' => '12',
            ],

        ];
        foreach ($data as $productList){
            $product = new Product();
            $product->setMainId($productList['mainId']);
            $product->setProductName($productList['productName']);
            $product->setDisplayName($productList['displayName']);
            $product->setDescription($productList['description']);
            $product->setPrice($productList['price']);
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
