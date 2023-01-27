<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\DTO\MainMenuDataTransferObject;
use App\Entity\MainCategorys;
use App\Entity\User;
use App\Model\Mapper\MainMenuMapper;
use App\Repository\UserRepository;
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
        $mainMenuDto = new MainMenuDataTransferObject(null,'test', 'test');
        $mainMenuEntityManager = new MainMenuEntityManager($this->entityManager);
        $mainMenuEntityManager->create($mainMenuDto);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $product = $em->getRepository(MainCategorys::class)->findOneBy(['mainCategoryName' => 'test']);
        $this->assertNotNull($product);
    }

    public function testProductSave():void
    {
        $mainCategory = $this->client->getContainer()->get(MainCategorysRepository::class)->findBy(['id' => 1]);
        $productMapper = new MainMenuMapper();
        $mainCategoryDto = $productMapper->mapToMainDto($mainCategory[0]);
        $newDTO = new MainMenuDataTransferObject($mainCategoryDto->mainId, $mainCategoryDto->mainCategoryName, $mainCategoryDto->displayName);
        $mainCategoryEntityManager = new MainMenuEntityManager($this->entityManager);
        $mainCategoryEntityManager->save($newDTO);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $mainCategory = $em->getRepository(MainCategorys::class)->findOneBy(['mainCategoryName' => 'test1']);
        self::assertNotNull($mainCategory);
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
