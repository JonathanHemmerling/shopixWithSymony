<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\MainCategorysRepository;
use App\Component\Product\Persistence\MainMenuEntityManager;
use App\Component\User\Persistence\Repository\UserRepository;
use App\DTO\MainMenuDataTransferObject;
use App\Entity\MainCategorys;
use App\Entity\User;

use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainMenuBusinessFascadeTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private MainCategorysRepository $mainCategorysRepository;
    private MainMenuBusinessFascade $fascade;
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createMainMenuData();
        $this->mainCategorysRepository = $this->client->getContainer()->get(MainCategorysRepository::class);
        $this->fascade = new MainMenuBusinessFascade(new MainMenuEntityManager($this->entityManager));

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

        $this->fascade->create($mainMenuDto);

        $product = $this->mainCategorysRepository->findOneBy(['id' => 3]);
        self::assertSame('test3', $product->getMainCategoryName());
        self::assertSame('test 3', $product->getDisplayName());
    }

    public function testMainMenuSave():void
    {
        $mainCategory = $this->client->getContainer()->get(MainCategorysRepository::class)->findOneBy(['id' => 1]);
        $newDTO = new MainMenuDataTransferObject();
        $newDTO->mainCategoryName = $mainCategory->getMainCategoryName();
        $newDTO->displayName = 'test one';

        $fascade = new MainMenuBusinessFascade(new MainMenuEntityManager($this->entityManager));
        $fascade->save($mainCategory, $newDTO);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $mainCategory = $em->getRepository(MainCategorys::class)->findOneBy(['id' => 1]);
        self::assertSame('test1', $mainCategory->getMainCategoryName());
        self::assertSame('test one', $mainCategory->getDisplayName());
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

}
