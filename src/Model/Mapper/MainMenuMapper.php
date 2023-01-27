<?php

declare(strict_types=1);

namespace App\Model\Mapper;


use App\Entity\MainCategorys;
use App\DTO\MainMenuDataTransferObject;

class MainMenuMapper implements MainMenuMapperInterface
{
    public function mapToMainDto(MainCategorys|array $list): MainMenuDataTransferObject
    {
        return new MainMenuDataTransferObject(
            $list->getId(),
            $list->getMainCategoryName(),
            $list->getDisplayName(),
        );
    }

}