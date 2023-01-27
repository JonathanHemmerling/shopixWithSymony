<?php

declare(strict_types=1);

namespace App\Model\Mapper;



use App\DTO\MainMenuDataTransferObject;
use App\Entity\MainCategorys;

interface MainMenuMapperInterface
{
    public function mapToMainDto(MainCategorys|array $list): MainMenuDataTransferObject;
}