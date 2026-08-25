<?php

declare(strict_types=1);

namespace App\Authentication\DTO;

class RequestPasswordResetDTO
{
    private string $email;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
