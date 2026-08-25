<?php

declare(strict_types=1);

namespace App\Tests\Authentication\DTO;

use App\Authentication\DTO\RegistrationDTO;
use PHPUnit\Framework\TestCase;

class RegistrationDTOTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $dto = new RegistrationDTO();

        $dto->setEmail('user@gmail.com');
        $dto->setPassword('password123');
        $dto->setConfirmPassword('password123');

        $this->assertEquals('user@gmail.com', $dto->getEmail());
        $this->assertEquals('password123', $dto->getPassword());
        $this->assertEquals('password123', $dto->getConfirmPassword());
    }

    public function testCreateFactoryMethod(): void
    {
        $dto = RegistrationDTO::create([
            'email' => 'test@example.com',
            'password' => 'password',
            'confirmPassword' => 'password',
        ]);

        $this->assertEquals('test@example.com', $dto->getEmail());
        $this->assertEquals('password', $dto->getPassword());
        $this->assertEquals('password', $dto->getConfirmPassword());
        $this->assertTrue($dto->passwordsMatch());
    }

    public function testNonMatchingPasswords(): void
    {
        $dto = new RegistrationDTO();

        $dto->setPassword('password');
        $dto->setConfirmPassword('password123');

        $this->assertFalse($dto->passwordsMatch());
    }

    public function testUserExistsDefaultsFalse(): void
    {
        $dto = new RegistrationDTO();

        $this->assertFalse($dto->userExists());
    }

    public function testSetUserExists(): void
    {
        $dto = new RegistrationDTO();

        $dto->setUserExists(true);

        $this->assertTrue($dto->userExists());
    }

    public function testValidateReturnsTrueWhenPasswordsMatchAndUserDoesNotExist(): void
    {
        $dto = new RegistrationDTO();

        $dto->setPassword('password123');
        $dto->setConfirmPassword('password123');
        $dto->setUserExists(false);

        $this->assertTrue($dto->validate());
    }

    public function testValidateReturnsFalseWhenPasswordsDoNotMatch(): void
    {
        $dto = new RegistrationDTO();

        $dto->setPassword('password123');
        $dto->setConfirmPassword('different');
        $dto->setUserExists(false);

        $this->assertFalse($dto->validate());
    }

    public function testValidateReturnsFalseWhenUserExists(): void
    {
        $dto = new RegistrationDTO();

        $dto->setPassword('password123');
        $dto->setConfirmPassword('password123');
        $dto->setUserExists(true);

        $this->assertFalse($dto->validate());
    }
}
