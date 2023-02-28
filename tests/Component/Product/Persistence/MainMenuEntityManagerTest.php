<?php

declare(strict_types=1);

namespace App\Tests\Component\Product\Persistence;

use App\Component\Category\Persistence\CategoryEntityManager;
use App\Component\User\Persistence\Repository\UserRepository;
use App\DTO\CategoryDataTransferObject;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class MainMenuEntityManagerTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private CategoryRepository $categoryRepository;
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createData();
        $this->createUserData();
        $this->categoryRepository = $this->client->getContainer()->get(CategoryRepository::class);
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
        $connection->executeUpdate('DELETE FROM category');
        $connection->executeUpdate('ALTER TABLE category AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM user');
        $connection->executeUpdate('ALTER TABLE user AUTO_INCREMENT=0');
        $this->entityManager = null;
    }

    public function testProductCreate(): void
    {
        $mainMenuDto = new CategoryDataTransferObject();
        $mainMenuDto->name = 'test3';
        $mainMenuEntityManager = new CategoryEntityManager($this->entityManager);
        $mainMenuEntityManager->create($mainMenuDto);

        $product = $this->categoryRepository->findOneBy(['id' => 3]);
        self::assertSame('test3', $product->getName());

    }

    public function testProductSave():void
    {
        $mainCategory = $this->categoryRepository->findBy(['id' => 1]);
        $newDTO = new CategoryDataTransferObject();
        $newDTO->name = 'Test1';

        $mainCategoryEntityManager = new CategoryEntityManager($this->entityManager);
        $mainCategoryEntityManager->save($mainCategory[0], $newDTO);

        $mainCategory = $this->categoryRepository->findOneBy(['id' => '1']);
        self::assertSame('Test1', $mainCategory->getName());
    }

    private function createData(): void
    {
        $data = [
            [
                'mainName' => 'test1',
            ],
            [
                'mainName' => 'test2',
            ],
        ];

        foreach ($data as $mainMenuData) {
            $mainCategory = new Category();
            $mainCategory->setName($mainMenuData['mainName']);
            $this->entityManager->persist($mainCategory);
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
