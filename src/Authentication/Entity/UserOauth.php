<?php

declare(strict_types=1);

namespace App\Authentication\Entity;

use App\Authentication\Repository\UserOauthRepository;
use App\Core\Entity\Application\AbstractBaseEntity;
use App\Core\Entity\OauthProvider;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserOauthRepository::class)]
#[ORM\Table(name: 'tblUserOauth', schema: 'Authentication')]
class UserOauth extends AbstractBaseEntity
{
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'oauthLinks')]
    #[ORM\JoinColumn(name: 'intUserId', referencedColumnName: 'intId', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: OauthProvider::class)]
    #[ORM\JoinColumn(name: 'intOauthProviderId', referencedColumnName: 'intId', nullable: false)]
    private OauthProvider $provider;

    #[ORM\Column(
        name: 'strOauthProviderId',
        type: 'string',
        length: 255,
        nullable: false,
        options: ['comment' => 'Provider user ID (e.g. GitHub user ID, Google sub)']
    )]
    private string $oauthProviderId;

    public function __construct(User $user, OauthProvider $provider, string $oauthProviderId)
    {
        $this->user = $user;
        $this->provider = $provider;
        $this->oauthProviderId = $oauthProviderId;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getProvider(): OauthProvider
    {
        return $this->provider;
    }

    public function getOauthProviderId(): string
    {
        return $this->oauthProviderId;
    }

    public function getProviderHandle(): string
    {
        return $this->provider->getHandle();
    }
}
