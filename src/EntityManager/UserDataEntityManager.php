<?php

namespace App\EntityManager;

use App\DTO\UserDataTransferObject;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class UserDataEntityManager
{
    public function __construct(private readonly EntityManager $entityManager)
    {

    }
    public function create(UserDataTransferObject $userData): void
    {
        $newUser = new User();
        $newUser->setEmail($userData->email);
        $newUser->setPassword($userData->password);
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();
    }
    public function save(UserDataTransferObject $userData): void
    {
        $editedUser = new User();
        $editedUser->setEmail($userData->email);
        $editedUser->setPassword($userData->password);
        $this->entityManager->persist($editedUser);
        $this->entityManager->flush();
    }
}