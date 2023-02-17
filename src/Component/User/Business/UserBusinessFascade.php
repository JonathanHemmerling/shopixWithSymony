<?php

declare(strict_types=1);

namespace App\Component\User\Business;

use App\Component\User\Persistence\EntityManager\UserDataEntityManager;
use App\DTO\UserDataTransferObject;
use App\Entity\User;


readonly class UserBusinessFascade
{
    public function __construct(private UserDataEntityManager $entityManager)
    {
    }

    public function create(UserDataTransferObject $dataTransferObject): void
    {
        $this->entityManager->create($dataTransferObject);
    }

    public function save(User $user, UserDataTransferObject $dataTransferObject): void
    {
        $this->entityManager->save($user, $dataTransferObject);
    }
}
