<?php

declare(strict_types=1);

namespace App\Component\User\Business;

use App\DTO\UserDataTransferObject;
use App\EntityManager\UserDataEntityManager;

class UserBusinessFascade
{

    public function __construct(private readonly UserDataEntityManager $entityManager)
    {
    }

    public function create(UserDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->create($dataTransferObject);
    }

    public function save(UserDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->save($dataTransferObject);
    }


//Entitymanager, Repository

//save Product-> call entitymanager and save-> mit DTO-> DTO Übergeben an FORM

//Im Test von Entitymanager testen
}