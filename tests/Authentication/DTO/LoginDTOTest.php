<?php

declare(strict_types=1);

namespace Authentication\DTO;

use App\Authentication\DTO\LoginDTO;
use PHPUnit\Framework\TestCase;

class LoginDTOTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $dto = new LoginDTO();

        $dto->setEmail('user@gmail.com');
        $dto->setPassword('password');

        $this->assertEquals('user@gmail.com', $dto->getEmail());
        $this->assertEquals('password', $dto->getPassword());
    }
}
