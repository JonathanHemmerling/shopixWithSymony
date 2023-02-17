<?php

declare(strict_types=1);

namespace App\Component\Attributes\Business;

use App\Component\Attributes\Business\AttributesBusinessFascade;
use App\Component\Attributes\Persistence\AttributesEntityManager;
use App\DTO\AttributesDataTransferObject;
use App\Repository\AttributesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AttributesBusinessFascadeTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
   private AttributesRepository $attributesRepository;
    private AttributesEntityManager $attributesEntityManager;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $container = $this->client->getContainer();
        $this->attributesRepository = $container->get(AttributesRepository::class);
        $this->attributesEntityManager = $container->get(AttributesEntityManager::class);
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $connection = $this->entityManager->getConnection();
        $connection->executeUpdate('DELETE FROM products');
        $connection->executeUpdate('ALTER TABLE products AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM attributes');
        $connection->executeUpdate('ALTER TABLE attributes AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM products_attributes');
        $connection->executeUpdate('ALTER TABLE products_attributes AUTO_INCREMENT=0');
        $connection->executeUpdate('DELETE FROM category');
        $connection->executeUpdate('ALTER TABLE category AUTO_INCREMENT=0');
        $this->entityManager->close();
    }
    public function testAttributeSave(): void
    {
        $this->createData();
        $attribute = $this->attributesRepository->findOneBy(['attribut' => 'test']);

        $attributeDTO = new AttributesDataTransferObject();
        $attributeDTO->attribute = 'Test';
        $attributeFascade = new AttributesBusinessFascade($this->attributesEntityManager);
        $attributeFascade->save($attribute, $attributeDTO);

        $attribute = $this->attributesRepository->findOneBy(['id' => 1]);
        self::assertSame('Test', $attribute->getAttribut());
    }

    private function createData():void
    {
        $data = new AttributesDataTransferObject('test');

        $this->attributesEntityManager->create($data);
    }
}
