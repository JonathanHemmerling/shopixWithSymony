<?php

declare(strict_types=1);

namespace App\Tests\Component\User\Communication\Controller;

use App\Component\Product\Persistence\ProductRepository;
use App\Component\User\Persistence\Repository\UserRepository;
use App\Entity\User;
use App\Repository\ProductsRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotAllowedControllerTest extends WebTestCase
{
    private ?ObjectManager $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createUserData();
        $this->productRepository = $this->client->getContainer()->get(ProductsRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $this->entityManager = null;
    }

    public function testUserIsNotAllowedInAdminSpace(): void
    {
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test2@test.de');
        $this->client->loginUser($testUser);
        $this->client->request('GET', '/admin/useroverview');

        self::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    private function createUserData(): void
    {
        $data = [
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