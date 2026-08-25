<?php

declare(strict_types=1);

namespace App\Authentication\Service\Email;

use App\Authentication\Entity\PasswordResetToken;
use App\Authentication\Entity\User;
use App\Core\Controller\EmailBuilder;
use App\Core\Entity\EmailType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class PasswordResetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private EmailBuilder $emailBuilder
    ) {
    }

    /**
     * @throws \Exception|TransportExceptionInterface
     */
    public function sendResetEmail(User $user): void
    {
        $token = new PasswordResetToken($user);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $this->mailer->send($this->emailBuilder->getEmail(
            EmailType::PASSWORD_RESET,
            $user->getEmail(),
            [
                'resetUrl' => $this->urlGenerator->generate(
                    'authenticate_password_reset',
                    ['token' => $token->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
            ]
        ));
    }

    public function validateToken(string $token): ?User
    {
        $tokenEntity = $this->entityManager->getRepository(PasswordResetToken::class)->findOneBy(['token' => $token]);

        if (!$tokenEntity || $tokenEntity->isExpired()) {
            return null;
        }

        return $tokenEntity->getUser();
    }

    public function consumeToken(string $token): void
    {
        $tokenEntity = $this->entityManager->getRepository(PasswordResetToken::class)->findOneBy(['token' => $token]);

        if ($tokenEntity) {
            $this->entityManager->remove($tokenEntity);
            $this->entityManager->flush();
        }
    }
}
