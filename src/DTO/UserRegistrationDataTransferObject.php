<?php

declare(strict_types=1);

namespace App\DTO;

class UserRegistrationDataTransferObject
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }
}
