<?php

declare(strict_types=1);

namespace App\Model\Mapper;

use App\DTO\UserDataTransferObject;
use App\Entity\User;


class UserDataMapper implements UserDataMapperInterface
{
    public function mapToUserDto(User|array $list): UserDataTransferObject
    {
        return new UserDataTransferObject(
            $list->getId(),
            $list->getEmail(),
            $list->getPassword(),
        );
    }

}