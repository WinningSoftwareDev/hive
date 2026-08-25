<?php

declare(strict_types=1);

namespace App\Authentication\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LoginDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email = '';

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, minMessage: 'Your password should be at least {{ limit }} characters long')]
    private string $password = '';

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
}
