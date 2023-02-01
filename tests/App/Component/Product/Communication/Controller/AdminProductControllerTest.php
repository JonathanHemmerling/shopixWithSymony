<?php

declare(strict_types=1);

namespace App\Component\Product\Communication\Controller;

use App\Component\Product\Persistence\ProductRepository;
use App\Entity\MainCategorys;
use App\Entity\Product;
use App\Entity\User;
use App\Component\User\Persistence\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminProductControllerTest extends WebTestCase
{
    private ?ObjectManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $connection->query('TRUNCATE main_categorys');
        $connection->query('TRUNCATE product');
        $this->createProductData();
        $this->createUserData();
        $this->createMainmenuData();
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
        $connection->query('TRUNCATE main_categorys');
        $connection->query('TRUNCATE product');
        $this->entityManager = null;
    }

    public function testAdminOverviewPresentation(): void
    {
        $crawler = $this->client->request('GET', '/entry');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $button = $crawler->filter('form > input');
        self::assertCount(2, $links);
        self::assertCount(1, $button);
        self::assertSelectorTextContains('h2', 'Adminarea');
    }

    public function testCategoryOverview()
    {
        $crawler = $this->client->request('GET', '/admin/mainmenu');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $list = $crawler->filter('li');
        $button = $crawler->filter('form > input');
        self::assertCount(3, $links);
        self::assertCount(2, $list);
        self::assertCount(1, $button);
        self::assertSelectorTextContains('h2', 'Productcategorys');
    }

    public function testProductOverviewForMainId()
    {
        $crawler = $this->client->request('GET', '/admin/allProducts/1');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        self::assertCount(3, $links);
        self::assertSelectorTextContains('h2', 'Products');
    }

    /*public function testProductOverviewForProductId()
    {
        $crawler = $this->client->request('GET', '/admin/product/2');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $fields = $crawler->filter('form');
        self::assertCount(2, $links);
        self::assertCount(1, $fields);
        self::assertSelectorTextContains('h2', 'Product');
    }*/

    public function testCreateProduct()
    {
        $this->client->request('POST', '/admin/createproduct/1', [
            'product_create_form' => [
                'mainId' => 1,
                'productName' => 'createTest',
                'displayName' => 'createTest',
                'description' => 'createTest',
                'price' => '0',
                'save' => '',
            ],
        ]);

        self::assertResponseStatusCodeSame(302);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(Product::class)->findOneBy(['productName' => 'createTest']);
        $this->assertSame('createTest', $product->getProductName());
        $this->assertSame('0', $product->getPrice());
    }

    public function testCreateNewProductEntryFail()
    {
        $this->client->request('POST', '/admin/createproduct/1', [
            [
                'mainId' => 1,
                'productName' => 'createTest',
                'displayName' => 'createTest',
                'description' => 'createTest',
                'price' => '0',
            ],
        ]);

        self::assertResponseStatusCodeSame(200);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(Product::class)->findOneBy(['productName' => 'createTest']);
        $this->assertNull($product);
    }

    public function testCreateNewMainCategoryEntry()
    {
        $this->client->request(
            'POST',
            '/admin/createcategory',
            [
                'main_category_create_form' => [
                    'mainCategoryName' => 'createTest',
                    'displayName' => 'createTest',
                    'save' => '',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(302);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $mainCategory = $em->getRepository(MainCategorys::class)->findOneBy(['mainCategoryName' => 'createTest']);
        $this->assertNotNull($mainCategory);
    }

    public function testCreateNewMainCategoryEntryFail()
    {
        $this->client->request(
            'POST',
            '/admin/createcategory', [
                [
                    'mainCategoryName' => 'createTest',
                    'displayName' => 'createTest',
                    'save' => '',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(200);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $mainCategory = $em->getRepository(MainCategorys::class)->findOneBy(['mainCategoryName' => 'createTest']);
        $this->assertNull($mainCategory);
    }

    public function testSaveEditedProductEntry()
    {
        $this->client->request(
            'POST',
            '/admin/product/1', [
                'product_save_form' =>
                    [
                        'mainId' => 1,
                        'productName' => 'createTest',
                        'displayName' => 'createTest',
                        'description' => 'createTest',
                        'price' => '0',
                        'save' => '',
                    ],
            ]
        );
        self::assertResponseStatusCodeSame(302);

        $response = $this->client->getResponse();
        self::assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
    }
    public function testSaveEditedProductEntryFail()
    {
        $this->client->request(
            'POST',
            '/admin/product/1',
                    [
                        'mainId' => 1,
                        'productName' => 'createTest',
                        'displayName' => 'createTest',
                        'description' => 'createTest',
                        'price' => '0',
                        'save' => '',
                    ]
        );
        self::assertResponseStatusCodeSame(200);

        $response = $this->client->getResponse();
        self::assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
    }


    public function testDeleteProductEntry()
    {
        $this->client->request('POST', 'admin/product/delete/2/2', []);
        self::assertResponseStatusCodeSame(302);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(Product::class)->findOneBy(['mainId' => 2]);
        $this->assertNull($product);
    }
    public function testDeleteProductEntryFail()
    {
        $this->client->request('POST', 'admin/product/delete/999/2', []);
        self::assertResponseStatusCodeSame(200);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(Product::class)->findOneBy(['mainId' => 2]);
        $this->assertNotNull($product);
    }

    private function createMainmenuData()
    {
        $data = [
            [
                'mainCategoryName' => 'jeans',
                'displayName' => 'Jeans',
            ],
            [
                'mainCategoryName' => 'pullover',
                'displayName' => 'Pullover',
            ],
        ];

        foreach ($data as $productData) {
            $product = new MainCategorys();
            $product->setMainCategoryName($productData['mainCategoryName']);
            $product->setDisplayName($productData['displayName']);
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();
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
