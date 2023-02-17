<?php

namespace App\Component\User\Persistence\EntityManager;

use App\DTO\UserDataTransferObject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserDataEntityManager
{
    public function __construct(private EntityManagerInterface $entityManager)
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
    public function save(User $user, UserDataTransferObject $userData): void
    {
        $editedUser = $user;
        $editedUser->setEmail($userData->email);
        $editedUser->setPassword($userData->password);
        $this->entityManager->persist($editedUser);
        $this->entityManager->flush();
    }
}