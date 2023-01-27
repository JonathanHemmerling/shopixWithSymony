<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRepositoryTest extends WebTestCase
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
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('user1@test.de');

        $this->client->loginUser($testUser);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $this->entityManager = null;
    }

   public function testHomepage():void
    {
        $crawler = $this->client->request('GET', '/admin/useroverview');
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h3', 'User Overview');
        $userList = $crawler->filter('a');
        self::assertCount(6, $userList);
    }

    private function createData(): void
    {
        $data = [
            [
                'email' => 'user1@test.de',
                'role' => 'ROLE_ADMIN',
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
