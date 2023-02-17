<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Category\Business\CategoryBusinessFascade;
use App\Component\Category\Persistence\CategoryEntityManager;
use App\Component\Product\Persistence\MainCategorysRepository;
use App\DTO\CategoryDataTransferObject;
use App\DTO\MainMenuDataTransferObject;
use App\Entity\Category;
use App\Entity\MainCategorys;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainMenuBusinessFascadeTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private CategoryRepository $categorysRepository;
    private CategoryBusinessFascade $fascade;
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createData();
        $this->categorysRepository = $this->client->getContainer()->get(CategoryRepository::class);
        $this->fascade = $this->client->getContainer()->get(CategoryBusinessFascade::class);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate('DELETE FROM products');
        $connection->executeUpdate('ALTER TABLE products AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM category');
        $connection->executeUpdate('ALTER TABLE category AUTO_INCREMENT=0');
        $this->entityManager = null;
    }

    public function testProductCreate(): void
    {
        $mainMenuDto = new CategoryDataTransferObject('test3');

        $this->fascade->create($mainMenuDto);

        $product = $this->categorysRepository->findOneBy(['id' => 3]);
        self::assertSame('test3', $product->getName());
    }

    public function testMainMenuSave():void
    {
        $mainCategory = $this->client->getContainer()->get(CategoryRepository::class)->findOneBy(['id' => 1]);
        $newDTO = new CategoryDataTransferObject('Test1');

        $this->fascade->save($mainCategory, $newDTO);

        $mainCategory = $this->categorysRepository->findOneBy(['id' => 1]);
        self::assertSame('Test1', $mainCategory->getName());}

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

        foreach ($data as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['mainName']);
            $this->entityManager->persist($category);
        }
        $this->entityManager->flush();
    }

}
