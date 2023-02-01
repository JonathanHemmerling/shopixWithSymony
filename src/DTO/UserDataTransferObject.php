<?php

declare(strict_types=1);

namespace App\DTO;

class UserDataTransferObject
{
    public int $id = 0;
    public string $email = '';
    public string $password = '';

    public function __construct(
    ) {
    }
}
