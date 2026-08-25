<?php

declare(strict_types=1);

namespace App\Authentication\Entity;

use App\Authentication\Repository\EmailVerificationTokenRepository;
use App\Core\Entity\Application\AbstractBaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;

#[ORM\Entity(repositoryClass: EmailVerificationTokenRepository::class)]
#[ORM\Table(name: 'tblEmailVerificationToken', schema: 'Authentication')]
class EmailVerificationToken extends AbstractBaseEntity
{
    #[ORM\Column(name: 'strToken', type: 'string', length: 100, unique: true)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'intUserId', referencedColumnName: 'intId', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'dtmExpires', type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    #[ORM\Column(name: 'dtmVerified', type: 'datetime')]
    private \DateTimeInterface $verifiedAt;

    /**
     * @throws RandomException
     */
    public static function create(User $user): self
    {
        $self = new self();

        $self->user = $user;
        $self->token = bin2hex(random_bytes(32));
        $self->expiresAt = new \DateTime('+1 day');

        return $self;
    }

    public function isExpired(): bool
    {
        return new \DateTime() > $this->expiresAt;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getVerifiedAt(): \DateTimeInterface
    {
        return $this->verifiedAt;
    }

    public function markVerified(): void
    {
        $this->verifiedAt = new \DateTime();
    }
}
