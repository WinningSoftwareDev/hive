<?php

declare(strict_types=1);

namespace App\Authentication\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDTO
{
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email address')]
    private string $email = '';

    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(min: 8, minMessage: 'Your password should be at least {{ limit }} characters long')]
    private string $password = '';

    #[Assert\NotBlank(message: 'Please confirm your password')]
    private string $confirmPassword = '';

    private bool $userExists = false;

    /**
     * @param array{
     *     email: string,
     *     password: string,
     *     confirmPassword: string,
     * } $data
     */
    public static function create(array $data): self
    {
        $dto = new self();

        $dto->email = $data['email'];
        $dto->password = $data['password'];
        $dto->confirmPassword = $data['confirmPassword'];

        return $dto;
    }

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

    public function userExists(): bool
    {
        return $this->userExists;
    }

    public function setUserExists(bool $userExists): void
    {
        $this->userExists = $userExists;
    }

    public function validate(): bool
    {
        return $this->passwordsMatch() && !$this->userExists();
    }
}
