<?php

declare(strict_types=1);

namespace App\Component\Product\Communication\Controller;

use App\Entity\Attributes;
use App\Entity\Category;
use App\Entity\Products;
use App\Entity\User;
use App\Component\User\Persistence\Repository\UserRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminProductControllerTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private ProductsRepository $productsRepository;
    private CategoryRepository $categoryRepository;

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
        $this->createAttributeData();
        $this->categoryRepository = $this->client->getContainer()->get(CategoryRepository::class);
        $this->productsRepository = $this->client->getContainer()->get(ProductsRepository::class);
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@test.de');
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
        self::assertCount(6, $links);
        self::assertCount(4, $list);
        self::assertCount(1, $button);
        self::assertSelectorTextContains('h2', 'Productcategorys');
    }

    public function testProductOverviewForMainId()
    {
        $crawler = $this->client->request('GET', '/admin/allProducts/2');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        self::assertCount(3, $links);
        self::assertSelectorTextContains('h2', 'Products');
    }

    public function testProductOverviewForProductId()
    {
        $crawler = $this->client->request('GET', '/admin/product/2');

        self::assertSame(500, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        $fields = $crawler->filter('form');
        self::assertCount(25, $links);
        self::assertCount(0, $fields);
        //self::assertSelectorTextContains('h2', 'Product');
    }

    public function testCreateProduct()
    {
        $this->client->request('POST', '/admin/createproduct/1', [
                'product_create_form' =>
                    [
                        'category' => 'test',
                        'productName' => 'createTest',
                        'description' => 'createTest',
                        'price' => 0,
                        ['attributes' => 'test1'],
                        'save' => '',
                    ],
        ]);

        self::assertResponseStatusCodeSame(200);
        $product = $this->productsRepository->findOneBy(['productName' => 'createTest']);
    }

    public function testCreateNewProductEntryFail()
    {
        $this->client->request('POST', '/admin/createproduct/1', [
            [
                'category' => 'test5',
                'productName' => 'createTest',
                'description' => 'createTest',
                'price' => 0,
                'attributes' => ['test1'],
                'save' => '',
            ],
        ]);

        self::assertResponseStatusCodeSame(200);
        $product = $this->productsRepository->findOneBy(['productName' => 'createTest']);
        $this->assertNull($product);
    }

    public function testCreateNewMainCategoryEntry()
    {
        $this->client->request(
            'POST',
            '/admin/createcategory',
            [
                'category_create_form' => [
                    'name' => 'createTest',
                    'save' => '',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(302);
        $mainCategory = $this->categoryRepository->findOneBy(['name' => 'createTest']);
        $this->assertNotNull($mainCategory);
    }

    public function testCreateNewMainCategoryEntryFail()
    {
        $this->client->request(
            'POST',
            '/admin/createcategory', [
                [
                    'name' => 'createTest',
                    'save' => '',
                ],
            ]
        );
        self::assertResponseStatusCodeSame(200);
        $mainCategory = $this->categoryRepository->findOneBy(['name' => 'createTest']);
        $this->assertNull($mainCategory);
    }

    public function testSaveEditedProductEntry()
    {
        $this->client->request(
            'POST',
            '/admin/product/1', [
                'product_save_form' =>
                    [
                        'category' => 'test1',
                        'productName' => 'createTest',
                        'description' => 'createTest',
                        'price' => 0,
                        'attributes' => ['test1'],
                        'save' => '',
                    ],
            ]
        );
      //  self::assertResponseStatusCodeSame(302);
        $product = $this->entityManager->getRepository(Products::class)->findOneBy(['id' => 1]);
       // self::assertSame(0, $product->getPrice());
    }

    public function testSaveEditedProductEntryFail()
    {
        $this->client->request(
            'POST',
            '/admin/product/1',
            ['product_save_form' =>
                [
                'category' => 'test',
                'productName' => 'createTest',
                'description' => 'createTest',
                'price' => 0,
                ['attribute' => 'test',
                'attribute2' => 'test',
                'attribute3' => 'test'],
                ],
            ]
        );
        //self::assertResponseStatusCodeSame(200);
        $product = $this->entityManager->getRepository(Products::class)->findOneBy(['id' => 1]);
        self::assertSame(1999, $product->getPrice());
    }

    public function testDeleteProductEntry()
    {
        $this->client->request('POST', 'admin/product/delete/2/test2/2', []);
        self::assertResponseStatusCodeSame(302);

        $product = $this->entityManager->getRepository(Products::class)->findOneBy(['id' => 2]);
        $this->assertNull($product);
    }
    public function testDeleteProductEntryFail()
    {
        $this->client->request('POST', 'admin/product/delete/999/test1/1', []);
        self::assertResponseStatusCodeSame(200);

        $product = $this->entityManager->getRepository(Products::class)->findOneBy(['id' => 2]);
        $this->assertNotNull($product);
    }

    private function createAttributeData(): void
    {
        $data = [
            [
                'attribute' => 'test1'
            ]
        ];
        foreach ($data as $attributeData) {
            $attribute = new Attributes();
            $attribute->setAttribut($attributeData['attribute']);
            $this->entityManager->persist($attribute);
        }
        $this->entityManager->flush();
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
