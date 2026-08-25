<?php

declare(strict_types=1);

namespace App\Authentication\Entity;

use App\Authentication\DTO\RegistrationDTO;
use App\Authentication\Repository\UserRepository;
use App\Core\Entity\Application\AbstractBaseEntity;
use App\Core\Entity\OauthProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @phpstan-type SerializedUser array{
 *     id: ?int,
 *     email: string,
 *     createdAt: string,
 *     verified: bool,
 *     oauthProviders: string[],
 * }
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'tblUser', schema: 'Authentication')]
class User extends AbstractBaseEntity implements UserInterface, PasswordAuthenticatedUserInterface, \JsonSerializable
{
    #[ORM\Column(name: 'strEmail', length: 180, unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(
        name: 'strPassword',
        length: 255,
        nullable: true,
        options: ['comment' => 'Hashed password (null for OAuth-only users)']
    )]
    private ?string $password = null;

    #[ORM\Column(name: 'bolActive', type: 'boolean', nullable: false)]
    private bool $isActive = true;

    #[ORM\Column(name: 'bolVerified', type: 'boolean', nullable: false)]
    private bool $isVerified = false;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'tblUserRole', schema: 'Authentication')]
    #[ORM\JoinColumn(name: 'intUserId', referencedColumnName: 'intId')]
    #[ORM\InverseJoinColumn(name: 'intRoleId', referencedColumnName: 'intId')]
    private Collection $roles;

    /**
     * @var Collection<int, UserOauth>
     */
    #[ORM\OneToMany(targetEntity: UserOauth::class, mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $oauthLinks;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->oauthLinks = new ArrayCollection();
    }

    public static function create(RegistrationDTO $dto, UserPasswordHasherInterface $passwordHasher): self
    {
        $user = new self();

        $user->setEmail($dto->getEmail());
        $user->setPassword($passwordHasher->hashPassword($user, $dto->getPassword()));

        return $user;
    }

    public function linkOauth(OauthProvider $provider, string $oauthProviderId): UserOauth
    {
        $userOauth = new UserOauth($this, $provider, $oauthProviderId);

        $this->oauthLinks->add($userOauth);

        return $userOauth;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @see UserInterface
     *
     * @throws \RuntimeException
     */
    public function getUserIdentifier(): string
    {
        if (empty($this->email)) {
            throw new \RuntimeException('Email is empty');
        }

        return $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles->map(fn (Role $role) => $role->getHandle())->toArray();

        return !empty($roles) ? $roles : ['ROLE_USER'];
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoleObjects(): Collection
    {
        return !$this->roles->isEmpty()
            ? $this->roles
            : new ArrayCollection([Role::getDefault()]);
    }

    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(Role $role): void
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return Collection<int, UserOauth>
     */
    public function getOauthLinks(): Collection
    {
        return $this->oauthLinks;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function verify(): void
    {
        $this->isVerified = true;
    }

    public function unverify(): void
    {
        $this->isVerified = false;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return SerializedUser
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'verified' => $this->isVerified(),
            'oauthProviders' => $this->oauthLinks->map(fn (UserOauth $link) => $link->getProviderHandle())->getValues(),
        ];
    }
}
