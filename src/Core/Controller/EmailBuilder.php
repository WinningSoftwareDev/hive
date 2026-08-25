<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\Entity\EmailType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime;

class EmailBuilder extends AbstractApplicationController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param array<string, mixed> $templateOptions
     *
     * @throws \Exception
     */
    public function getEmail(string $emailTypeHandle, string $to, array $templateOptions = []): Mime\Email
    {
        $emailType = $this->entityManager->getRepository(EmailType::class)->findOneBy(['handle' => $emailTypeHandle]);

        if (!$emailType instanceof EmailType) {
            throw new \RuntimeException('Email type not found');
        }

        $mailFromAddress = $_ENV['MAIL_FROM_ADDRESS'];
        $mailFromName = $_ENV['MAIL_FROM_NAME'];

        if (!is_string($mailFromAddress) || !is_string($mailFromName)) {
            throw new \RuntimeException('Mail from address or name is not set in your .env file');
        }

        $emailContent = $this->renderTemplate($emailType->getTemplate(), $templateOptions)->getContent();

        if (!is_string($emailContent)) {
            throw new \RuntimeException('Email template is not valid');
        }

        return new Mime\Email()
            ->from(new Mime\Address($mailFromAddress, $mailFromName))
            ->to($to)
            ->subject($emailType->getName())
            ->html($emailContent);
    }
}
