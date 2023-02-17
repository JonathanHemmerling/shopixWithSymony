<?php

declare(strict_types=1);

namespace App\Component\Attributes\Persistence;

use App\DTO\AttributesDataTransferObject;
use App\Entity\Attributes;
use Doctrine\ORM\EntityManagerInterface;

use function PHPUnit\Framework\isEmpty;

readonly class AttributesEntityManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function create(AttributesDataTransferObject $attributesData): void
    {
        $newAttribute = new Attributes();
        $newAttribute->setAttribut($attributesData->attribute);
        $this->entityManager->persist($newAttribute);
        $this->entityManager->flush();
    }

    public function save(Attributes $attribute, AttributesDataTransferObject $attributesDTO): void
    {
        $savedAttribute = $attribute;
        $savedAttribute->setAttribut($attributesDTO->attribute);
        $this->entityManager->persist($savedAttribute);
        $this->entityManager->flush();
    }
}