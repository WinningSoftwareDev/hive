<?php

declare(strict_types=1);

namespace App\Tests\Authentication\Service\Email;

use App\Authentication\Entity\EmailVerificationToken;
use App\Authentication\Entity\User;
use App\Authentication\Service\Email\EmailVerificationService;
use App\Core\Controller\EmailBuilder;
use App\Core\Entity\EmailType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailVerificationServiceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testSendVerificationEmailPersistsNewTokenAndSendsEmail(): void
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
            ->with($this->callback(function (EmailVerificationToken $token) use (&$persistedToken, $user): bool {
                $persistedToken = $token;

                return $token->getUser() === $user;
            }));

        $entityManager->expects($this->once())
            ->method('flush');

        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'authenticate_verify_email',
                $this->callback(fn (array $params): bool => isset($params['token']) && is_string($params['token'])),
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://example.com/verify?token=abc');

        $emailBuilder->expects($this->once())
            ->method('getEmail')
            ->with(
                EmailType::VERIFY_EMAIL_ADDRESS,
                $user->getEmail(),
                ['verificationUrl' => 'https://example.com/verify?token=abc']
            )
            ->willReturn($email);

        $mailer->expects($this->once())
            ->method('send')
            ->with($email);

        $service = new EmailVerificationService($entityManager, $mailer, $urlGenerator, $emailBuilder);
        $service->sendVerificationEmail($user);

        $this->assertInstanceOf(EmailVerificationToken::class, $persistedToken);
    }

    /**
     * @throws \Exception
     */
    public function testSendVerificationEmailWithExistingTokenDoesNotPersist(): void
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $existingToken = EmailVerificationToken::create($user);
        $email = new Email();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(MailerInterface::class);
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $emailBuilder = $this->createMock(EmailBuilder::class);

        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                'authenticate_verify_email',
                ['token' => $existingToken->getToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn('https://example.com/verify?token=existing');

        $emailBuilder->expects($this->once())
            ->method('getEmail')
            ->with(
                EmailType::VERIFY_EMAIL_ADDRESS,
                $user->getEmail(),
                ['verificationUrl' => 'https://example.com/verify?token=existing']
            )
            ->willReturn($email);

        $mailer->expects($this->once())
            ->method('send')
            ->with($email);

        $service = new EmailVerificationService($entityManager, $mailer, $urlGenerator, $emailBuilder);
        $service->sendVerificationEmail($user, $existingToken);
    }

    /**
     * @throws \Exception
     */
    public function testVerifyTokenMarksTokenAndUserVerified(): void
    {
        $user = new User();
        $tokenEntity = EmailVerificationToken::create($user);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'valid-token'])
            ->willReturn($tokenEntity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(EmailVerificationToken::class)
            ->willReturn($repository);

        $entityManager->expects($this->once())
            ->method('flush');

        $service = $this->makeService($entityManager);
        $result = $service->verifyToken('valid-token');

        $this->assertSame($user, $result);
        $this->assertTrue($user->isVerified());
        $this->assertInstanceOf(\DateTimeInterface::class, $tokenEntity->getVerifiedAt());
    }

    public function testVerifyTokenReturnsNullWhenTokenNotFound(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'missing-token'])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(EmailVerificationToken::class)
            ->willReturn($repository);

        $entityManager->expects($this->never())
            ->method('flush');

        $service = $this->makeService($entityManager);

        $this->assertNull($service->verifyToken('missing-token'));
    }

    /**
     * @throws \Exception
     */
    public function testVerifyTokenReturnsNullWhenTokenExpired(): void
    {
        $user = new User();
        $tokenEntity = EmailVerificationToken::create($user);

        // Force the token into an expired state.
        $reflection = new \ReflectionProperty(EmailVerificationToken::class, 'expiresAt');
        $reflection->setValue($tokenEntity, new \DateTime('-1 hour'));

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['token' => 'expired-token'])
            ->willReturn($tokenEntity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(EmailVerificationToken::class)
            ->willReturn($repository);

        $entityManager->expects($this->never())
            ->method('flush');

        $service = $this->makeService($entityManager);

        $this->assertNull($service->verifyToken('expired-token'));
        $this->assertFalse($user->isVerified());
    }

    /**
     * Builds the service with stubbed collaborators that are irrelevant to the
     * token verification tests, so PHPUnit does not flag them as mocks without expectations.
     */
    private function makeService(EntityManagerInterface $entityManager): EmailVerificationService
    {
        return new EmailVerificationService(
            $entityManager,
            $this->createStub(MailerInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(EmailBuilder::class)
        );
    }
}
