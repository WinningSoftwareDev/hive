<?php

declare(strict_types=1);

namespace App\Tests\Core\Classes\HealthCheck;

use App\Authentication\Entity\User;
use App\Core\Classes\HealthCheck\DatabaseSetupCheck;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use PHPUnit\Framework\TestCase;

class DatabaseSetupCheckTest extends TestCase
{
    public function testGetLabel(): void
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $check = new DatabaseSetupCheck($entityManager);

        $this->assertEquals('Database setup', $check->getLabel());
    }

    /**
     * @throws \Exception
     */
    public function testCheckReturnsSuccessWhenDatabaseIsSetUp(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->willReturn(new User());

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $check = new DatabaseSetupCheck($entityManager);
        $result = $check->check();

        $this->assertInstanceOf(Success::class, $result);
        $this->assertEquals('Default database tables exist', $result->getMessage());
    }

    /**
     * @throws \Exception
     */
    public function testCheckReturnsFailureWhenDatabaseIsNotSetUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->willThrowException(new \Exception('Table not found'));

        $check = new DatabaseSetupCheck($entityManager);
        $result = $check->check();

        $this->assertInstanceOf(Failure::class, $result);
        $this->assertEquals('Default tables do not exist. Run: bin/console app:database:setup', $result->getMessage());
    }
}
