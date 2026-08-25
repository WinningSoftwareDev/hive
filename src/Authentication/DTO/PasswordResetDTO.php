<?php

declare(strict_types=1);

namespace App\Authentication\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, minMessage: 'Your password should be at least {{ limit }} characters long')]
    private string $password = '';

    #[Assert\NotBlank]
    private string $confirmPassword = '';

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): void
    {
        $this->confirmPassword = $confirmPassword;
    }

    public function passwordsMatch(): bool
    {
        return $this->password === $this->confirmPassword;
    }

    public function validate(): bool
    {
        return $this->passwordsMatch();
    }
}
