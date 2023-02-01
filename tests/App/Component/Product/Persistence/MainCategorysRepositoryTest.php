<?php

declare(strict_types=1);

namespace App\Component\Product\Persistence;

use App\Entity\MainCategorys;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainCategorysRepositoryTest extends WebTestCase
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
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate('DELETE FROM main_categorys');
        $connection->executeUpdate('ALTER TABLE main_categorys AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM user');
        $connection->executeUpdate('ALTER TABLE user AUTO_INCREMENT=0');
        $this->entityManager = null;
    }

    public function testHomepage():void
    {
        $crawler = $this->client->request('GET', '/entry');

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h2', 'Productcategorys');

        $categoryList = $crawler->filter('ul.mainMenu > li > a');
        self::assertCount(2, $categoryList);
        $listElement = $categoryList->getNode(0);
        self::assertSame('Jeans', $listElement->nodeValue);
        $listElement = $categoryList->getNode(1);
        self::assertSame('Pullover', $listElement->nodeValue);
    }

    private function createData(): void
    {
        $data = [
            [
                'mainCategoryName' => 'jeans',
                'displayName' => 'Jeans'
            ],
            [
                'mainCategoryName' => 'pullover',
                'displayName' => 'Pullover'
            ],
        ];

        foreach ($data as $categoryList){
            $mainCategory = new MainCategorys();

            $mainCategory->setMainCategoryName($categoryList['mainCategoryName']);
            $mainCategory->setDisplayName($categoryList['displayName']);
            $this->entityManager->persist($mainCategory);
        }

        $this->entityManager->flush();

    }
    private function createUserData(): void
    {
        $data = [
            [
                'email' => 'admin@test.de',
                'role' => 'ROLE_ADMIN',
                'password' => 'password'
            ],
            [
                'email' => 'test2@test.de',
                'role' => 'ROLE_USER',
                'password' => 'password'
            ]
        ];

        foreach ($data as $userData){
            $user = new User();

            $user->setEmail($userData['email']);
            $user->setPassword($userData['password']);
            $user->setRoles([$userData['role']]);
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
    }



}
