<?php

declare(strict_types=1);

namespace App\DTO;

class UserDataTransferObject
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}
