<?php

declare(strict_types=1);

namespace App\Component\User\Persistence\EntityManager;

use App\Component\User\Business\UserBusinessFascade;
use App\DTO\UserDataTransferObject;
use App\Entity\User;
use App\Component\User\Persistence\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class UserEntityManagerTest extends WebTestCase
{

    private ?ObjectManager $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->createUserData();
        $this->userRepository = $this->client->getContainer()->get(UserRepository::class);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->query('TRUNCATE user');
        $connection->query('TRUNCATE product');
        $this->entityManager = null;
    }

    public function testUserCreate(): void
    {
        $userDto = new UserDataTransferObject();
        $userDto->email = 'test1@test.de';
        $userDto->password = 'password';
        $userEntityManager = new UserDataEntityManager($this->entityManager);
        $userEntityManager->create($userDto);

        $user= $this->userRepository->findOneBy(['id' => 3]);
        $this->assertSame('test1@test.de', $user->getEmail());
        $this->assertSame('password', $user->getPassword());
    }

    public function testUserSave():void
    {
        $user = $this->userRepository->findOneBy(['id' => 1]);
        $newDTO = new UserDataTransferObject();
        $newDTO->email = 't1@test.de';
        $newDTO->password = $user->getPassword();

        $productEntityManager = new UserDataEntityManager($this->entityManager);
        $productEntityManager->save($user, $newDTO);

        $user = $this->userRepository->findOneBy(['id' => 1]);
        self::assertSame('t1@test.de', $user->getEmail());
        self::assertSame('password', $user->getPassword());
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
