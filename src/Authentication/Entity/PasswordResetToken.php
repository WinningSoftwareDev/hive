<?php

declare(strict_types=1);

namespace App\Authentication\Entity;

use App\Core\Entity\Application\AbstractBaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;

#[ORM\Entity]
#[ORM\Table(name: 'tblPasswordResetToken', schema: 'Authentication')]
#[ORM\Index(name: 'I_tblPasswordResetToken_intUserId', columns: ['intUserId'])]
class PasswordResetToken extends AbstractBaseEntity
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'intUserId', referencedColumnName: 'intId', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'strToken', type: 'string', length: 100, unique: true)]
    private string $token;

    #[ORM\Column(name: 'dtmExpires', type: 'datetime')]
    private \DateTimeInterface $expiresAt;

    /**
     * @throws RandomException
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->token = bin2hex(random_bytes(32));
        $this->expiresAt = new \DateTime('+1 hour');
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return new \DateTime() > $this->expiresAt;
    }
}
