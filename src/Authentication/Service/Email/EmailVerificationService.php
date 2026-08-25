<?php

declare(strict_types=1);

namespace App\Authentication\Service\Email;

use App\Authentication\Entity\EmailVerificationToken;
use App\Authentication\Entity\User;
use App\Core\Controller\EmailBuilder;
use App\Core\Entity\EmailType;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class EmailVerificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private EmailBuilder $emailBuilder
    ) {
    }

    /**
     * @throws \Exception|TransportExceptionInterface|RandomException
     */
    public function sendVerificationEmail(User $user, ?EmailVerificationToken $existingToken = null): void
    {
        $token = $existingToken ?? EmailVerificationToken::create($user);

        if (!$existingToken) {
            $this->entityManager->persist($token);
            $this->entityManager->flush();
        }

        $this->mailer->send($this->emailBuilder->getEmail(
            EmailType::VERIFY_EMAIL_ADDRESS,
            $user->getEmail(),
            [
                'verificationUrl' => $this->urlGenerator->generate(
                    'authenticate_verify_email',
                    ['token' => $token->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
        ));
    }

    public function verifyToken(string $token): ?User
    {
        $tokenEntity = $this->entityManager->getRepository(EmailVerificationToken::class)
            ->findOneBy(['token' => $token]);

        if (!$tokenEntity || $tokenEntity->isExpired()) {
            return null;
        }

        $user = $tokenEntity->getUser();

        $tokenEntity->markVerified();
        $user->verify();

        $this->entityManager->flush();

        return $user;
    }
}
