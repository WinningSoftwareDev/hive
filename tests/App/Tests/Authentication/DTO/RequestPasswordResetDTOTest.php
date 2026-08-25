<?php

declare(strict_types=1);

namespace App\Tests\Authentication\DTO;

use App\Authentication\DTO\RequestPasswordResetDTO;
use PHPUnit\Framework\TestCase;

class RequestPasswordResetDTOTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $dto = new RequestPasswordResetDTO();

        $dto->setEmail('user@gmail.com');

        $this->assertEquals('user@gmail.com', $dto->getEmail());
    }
}
