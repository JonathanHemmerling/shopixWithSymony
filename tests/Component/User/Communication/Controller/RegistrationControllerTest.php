<?php

declare(strict_types=1);

namespace App\Tests\Component\User\Communication\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    protected function setUp():void
    {
        parent::setUp();
        $this->client = self::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
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
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h3', 'Register');

        $registerInput = $crawler->filter('input');
        self::assertCount(3, $registerInput);
        $links = $crawler->filter('a');
        self::assertCount(1, $links);
        $button = $crawler->filter('button');
        self::assertCount(1, $button);

    }

    public function testIfUserDataIsStoredInDatabase(): void
    {
        $this->client->request(
            'POST',
            '/register', [
                'registration_form' =>
                    ['email' => 'test4@test.de', 'plainPassword' => 'password', 'agreeTerms' => '1'],
            ]
        );

        self::assertResponseStatusCodeSame(302);
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'test4@test.de']);
        $this->assertNotNull($user);
    }
}
