<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\MainCategorysRepository;
use App\Component\Product\Persistence\MainMenuEntityManager;
use App\Component\Product\Persistence\ProductEntityManager;
use App\Component\Product\Persistence\ProductRepository;
use App\DTO\MainMenuDataTransferObject;
use App\DTO\ProductsDataTransferObject;
use App\Entity\MainCategorys;
use App\Entity\Product;
use App\Entity\User;
use App\Model\Mapper\MainMenuMapper;
use App\Model\Mapper\ProductsMapper;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainMenuBusinessFascadeTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createMainMenuData();
        $this->createUserData();
        $this->productRepository = $this->client->getContainer()->get(MainCategorysRepository::class);
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
        $mainMenuDto = new MainMenuDataTransferObject(null, 'test', 'test');
        $fascade = new MainMenuBusinessFascade(new MainMenuEntityManager($this->entityManager));
        $fascade->create($mainMenuDto);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $mainCategory = $em->getRepository(MainCategorys::class)->findOneBy(['mainCategoryName' => 'test']);
        $this->assertNotNull($mainCategory);
    }

    public function testProductSave():void
    {
        $mainCategory = $this->client->getContainer()->get(MainCategorysRepository::class)->findBy(['id' => 1]);
        $mainCategoryMapper = new MainMenuMapper();
        $mainCategoryDto = $mainCategoryMapper->mapToMainDto($mainCategory[0]);
        $newDTO = new MainMenuDataTransferObject($mainCategoryDto->mainId, $mainCategoryDto->mainCategoryName, $mainCategoryDto->displayName);
        $fascade = new MainMenuBusinessFascade(new MainMenuEntityManager($this->entityManager));
        $fascade->save($newDTO);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $mainCategory = $em->getRepository(MainCategorys::class)->findOneBy(['mainCategoryName' => 'test1']);
        self::assertNotNull($mainCategory);
    }

    private function createMainMenuData(): void
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

        foreach ($data as $mainMenuData) {
            $mainCategory = new MainCategorys();
            $mainCategory->setMainCategoryName($mainMenuData['mainCategoryName']);
            $mainCategory->setDisplayName($mainMenuData['displayName']);
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
