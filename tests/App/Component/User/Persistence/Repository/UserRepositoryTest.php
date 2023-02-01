<?php

declare(strict_types=1);

namespace App\Component\User\Persistence\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRepositoryTest extends WebTestCase
{
    private ?ObjectManager $entityManager;
    private KernelBrowser $client;
    private EntityManager $em;

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
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
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

    public function testFind():void
    {
        $find= $this->em->getRepository(User::class)->find(1);
        $this->assertSame('user1@test.de', $find->getEmail());
        $this->assertSame('123456789', $find->getPassword());

        $findOneBy= $this->em->getRepository(User::class)->findOneBy(['id' => 1]);
        $this->assertSame('user1@test.de', $findOneBy->getEmail());
        $this->assertSame('123456789', $findOneBy->getPassword());

        $findAll= $this->em->getRepository(User::class)->findAll();
        $this->assertSame('user2@test.de', $findAll[1]->getEmail());
        $this->assertSame('987654321', $findAll[1]->getPassword());

        $findBy= $this->em->getRepository(User::class)->findBy(['email' => 'user1@test.de']);
        $this->assertSame(1, $findBy[0]->getId());
        $this->assertSame('123456789', $findBy[0]->getPassword());
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
