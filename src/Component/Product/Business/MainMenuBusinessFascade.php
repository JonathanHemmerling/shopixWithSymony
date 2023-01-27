<?php

declare(strict_types=1);

namespace App\Component\Product\Business;

use App\Component\Product\Persistence\MainMenuEntityManager;
use App\DTO\MainMenuDataTransferObject;

class MainMenuBusinessFascade
{
    public function __construct(private readonly MainMenuEntityManager $entityManager)
    {
    }

    public function create(MainMenuDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->create($dataTransferObject);
    }

    public function save(MainMenuDataTransferObject $dataTransferObject):void
    {
        $this->entityManager->save($dataTransferObject);
    }

//save Product-> call entitymanager and save-> mit DTO-> DTO Übergeben an FORM

//Im Test von Entitymanager testen
}