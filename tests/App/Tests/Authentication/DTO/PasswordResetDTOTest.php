<?php

declare(strict_types=1);

namespace App\Tests\Authentication\DTO;

use App\Authentication\DTO\PasswordResetDTO;
use PHPUnit\Framework\TestCase;

class PasswordResetDTOTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $dto = new PasswordResetDTO();

        $dto->setPassword('password');
        $dto->setConfirmPassword('password');

        $this->assertEquals('password', $dto->getPassword());
        $this->assertEquals('password', $dto->getConfirmPassword());
        $this->assertTrue($dto->passwordsMatch());
    }

    public function testNonMatchingPasswords(): void
    {
        $dto = new PasswordResetDTO();

        $dto->setPassword('password');
        $dto->setConfirmPassword('password123');

        $this->assertFalse($dto->passwordsMatch());
        $this->assertFalse($dto->validate());
    }
}
