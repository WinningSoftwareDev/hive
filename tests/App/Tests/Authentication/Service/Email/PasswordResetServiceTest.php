<?php

declare(strict_types=1);

namespace App\Tests\Authentication\Service\Email;

use App\Authentication\Entity\PasswordResetToken;
use App\Authentication\Entity\User;
use App\Authentication\Service\Email\PasswordResetService;
use App\Core\Controller\EmailBuilder;
use App\Core\Entity\EmailType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testSendResetEmailPersistsTokenAndSendsEmail(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $email = new Email();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $emailBuilder = $this->createMock(EmailBuilder::class);

        $persistedToken = null;
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (PasswordResetToken $token) use (&$persistedToken, $user): bool {
                $persistedToken = $token;

                return $token->getUser() === $user;
            }));

        $entityManager->expects($this->once())
            ->method('flush');

        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'authenticate_password_reset',
                $this->callback(fn (array $params): bool => isset($params['token']) && is_string($params['token'])),
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://example.com/reset?token=abc');

        $emailBuilder->expects($this->once())
            ->method('getEmail')
            ->with(
                EmailType::PASSWORD_RESET,
                $user->getEmail(),
                ['resetUrl' => 'https://example.com/reset?token=abc']
            )
            ->willReturn($email);

        $mailer->expects($this->once())
            ->method('send')
            ->with($email);

        $service = new PasswordResetService($entityManager, $mailer, $urlGenerator, $emailBuilder);
        $service->sendResetEmail($user);

        $this->assertInstanceOf(PasswordResetToken::class, $persistedToken);
    }

    public function testValidateTokenReturnsUserForValidToken(): void
    {
        $user = new User();
        $tokenEntity = new PasswordResetToken($user);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'valid-token'])
            ->willReturn($tokenEntity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(PasswordResetToken::class)
            ->willReturn($repository);

        $service = $this->makeService($entityManager);

        $this->assertSame($user, $service->validateToken('valid-token'));
    }

    public function testValidateTokenReturnsNullWhenTokenNotFound(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'missing-token'])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(PasswordResetToken::class)
            ->willReturn($repository);

        $service = $this->makeService($entityManager);

        $this->assertNull($service->validateToken('missing-token'));
    }

    /**
     * @throws \Exception
     */
    public function testValidateTokenReturnsNullWhenTokenExpired(): void
    {
        $user = new User();
        $tokenEntity = new PasswordResetToken($user);

        // Force the token into an expired state.
        $reflection = new \ReflectionProperty(PasswordResetToken::class, 'expiresAt');
        $reflection->setValue($tokenEntity, new \DateTime('-1 hour'));

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'expired-token'])
            ->willReturn($tokenEntity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(PasswordResetToken::class)
            ->willReturn($repository);

        $service = $this->makeService($entityManager);

        $this->assertNull($service->validateToken('expired-token'));
    }

    public function testConsumeTokenRemovesExistingToken(): void
    {
        $tokenEntity = new PasswordResetToken(new User());

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'some-token'])
            ->willReturn($tokenEntity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(PasswordResetToken::class)
            ->willReturn($repository);

        $entityManager->expects($this->once())
            ->method('remove')
            ->with($tokenEntity);

        $entityManager->expects($this->once())
            ->method('flush');

        $service = $this->makeService($entityManager);
        $service->consumeToken('some-token');
    }

    public function testConsumeTokenDoesNothingWhenTokenNotFound(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'missing-token'])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(PasswordResetToken::class)
            ->willReturn($repository);

        $entityManager->expects($this->never())
            ->method('remove');

        $entityManager->expects($this->never())
            ->method('flush');

        $service = $this->makeService($entityManager);
        $service->consumeToken('missing-token');
    }

    private function makeService(EntityManagerInterface $entityManager): PasswordResetService
    {
        return new PasswordResetService(
            $entityManager,
            $this->createStub(MailerInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(EmailBuilder::class)
        );
    }
}
