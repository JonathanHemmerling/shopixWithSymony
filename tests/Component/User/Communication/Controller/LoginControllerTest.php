<?php

declare(strict_types=1);

namespace App\Tests\Component\User\Communication\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

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
    }

    public function testHomepage():void
    {
        $crawler = $this->client->request('GET', '/login');
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('h3', 'Login');

        $loginInput = $crawler->filter('input');
        self::assertCount(4, $loginInput);
        $links = $crawler->filter('a');
        self::assertCount(1, $links);
    }

}
