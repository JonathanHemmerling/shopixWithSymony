<?php

declare(strict_types=1);

namespace App\Model\Mapper;

use App\DTO\UserDataTransferObject;
use App\Entity\User;


interface UserDataMapperInterface
{
    public function mapToUserDto(User|array $list): UserDataTransferObject;
}