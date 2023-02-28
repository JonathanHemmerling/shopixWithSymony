<?php

declare(strict_types=1);

namespace App\Tests\Component\User\Communication\Controller;

use App\Component\Product\Persistence\ProductRepository;
use App\Component\User\Persistence\Repository\UserRepository;
use App\Entity\User;
use App\Repository\ProductsRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserControllerTest extends WebTestCase
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
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@test.de');

        $this->client->loginUser($testUser);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $this->entityManager = null;
    }

    public function testUserOverview(): void
    {
        $crawler = $this->client->request('GET', '/admin/useroverview');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $links = $crawler->filter('a');
        self::assertCount(6, $links);
        self::assertSelectorTextContains('h3', 'User Overview');
    }

    public function testCreateNewUserEntry(): void
    {
        $this->client->request(
            'POST',
            '/admin/createUser', [
                'new_user_form' =>
                    ['email' => 'createTest', 'password' => 'password', 'save' => ''],
            ]
        );

        self::assertResponseStatusCodeSame(302);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'createTest']);
        $this->assertNotNull($user);
    }

    public function testCreateNewUserEntryFail(): void
    {
        $this->client->request(
            'POST',
            '/admin/createUser', [
                ['email' => 'createTest', 'password' => 'password'],
            ]
        );

        self::assertResponseStatusCodeSame(200);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'createTest']);
        $this->assertNull($user);
    }
    public function testSafeEditedUserEntry(): void
    {
        $this->client->request(
            'POST',
            '/admin/user/1', [
                'save_user_form' =>
                    [
                        'email' => 'createTest@test.de',
                        'password' => 'createTest',
                        'save' => '',
                    ],
            ]
        );
        self::assertResponseStatusCodeSame(302);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'createTest@test.de']);
        $this->assertNotNull($user);
        $response = $this->client->getResponse();
        self::assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
    }
    public function testSafeEditedUserEntryFail(): void
    {
        $this->client->request(
            'POST',
            '/admin/user/1', [
                    [
                        'email' => 'createTest@test.de',
                        'password' => 'createTest',
                        'save' => '',
                    ],
            ]
        );
        self::assertResponseStatusCodeSame(200);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'createTest@test.de']);
        $this->assertNull($user);
    }

    public function testDeleteUserEntry(): void
    {
        $this->client->request('POST', 'admin/user/delete/2', []);
        self::assertResponseStatusCodeSame(200);

        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['id' => 3]);
        $this->assertNull($user);
    }

    private function createUserData(): void
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
