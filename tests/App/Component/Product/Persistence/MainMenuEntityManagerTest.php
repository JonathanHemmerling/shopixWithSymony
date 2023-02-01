<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\Component\User\Persistence\Repository\UserRepository;
use App\DTO\MainMenuDataTransferObject;
use App\Entity\MainCategorys;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class MainMenuEntityManagerTest extends WebTestCase
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
        $connection->query('TRUNCATE main_categorys');
        $this->entityManager = null;
    }

    public function testProductCreate(): void
    {
        $mainMenuDto = new MainMenuDataTransferObject();
        $mainMenuDto->mainCategoryName = 'test3';
        $mainMenuDto->displayName = 'test 3';
        $mainMenuEntityManager = new MainMenuEntityManager($this->entityManager);
        $mainMenuEntityManager->create($mainMenuDto);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(MainCategorys::class)->findOneBy(['id' => 3]);
        self::assertSame('test3', $product->getMainCategoryName());
        self::assertSame('test 3', $product->getDisplayName());

    }

    public function testProductSave():void
    {
        $mainCategory = $this->client->getContainer()->get(MainCategorysRepository::class)->findBy(['id' => 1]);
        $newDTO = new MainMenuDataTransferObject();
        $newDTO->mainCategoryName = $mainCategory[0]->getMainCategoryName();
        $newDTO->displayName = 'test one';

        $mainCategoryEntityManager = new MainMenuEntityManager($this->entityManager);
        $mainCategoryEntityManager->save($mainCategory[0], $newDTO);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $mainCategory = $em->getRepository(MainCategorys::class)->findOneBy(['id' => '1']);
        self::assertSame('test1', $mainCategory->getMainCategoryName());
        self::assertSame('test one', $mainCategory->getDisplayName());
    }


    private function createProductData()
    {
        $data = [
            [
                'mainCategoryName' => 'test1',
                'displayName' => 'test1',
            ],
            [
                'mainCategoryName' => 'test2',
                'displayName' => 'test2',
            ],
        ];

        foreach ($data as $productData) {
            $mainCategory = new MainCategorys();

            $mainCategory->setMainCategoryName($productData['mainCategoryName']);
            $mainCategory->setDisplayName($productData['displayName']);
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
