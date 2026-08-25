<?php

declare(strict_types=1);

namespace App\Authentication\Entity;

use App\Core\Entity\Application\AbstractLookupEntity;
use Doctrine\Common\Collections;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ublRole', schema: 'Authentication')]
class Role extends AbstractLookupEntity
{
    public const string ROLE_ADMIN = 'ROLE_ADMIN';
    public const string ROLE_USER = 'ROLE_USER';

    /**
     * @var Collections\Collection<int, Permission>
     */
    #[ORM\ManyToMany(targetEntity: Permission::class)]
    #[ORM\JoinTable(name: 'tblRolePermission', schema: 'Authentication')]
    #[ORM\JoinColumn(name: 'intRoleId', referencedColumnName: 'intId')]
    #[ORM\InverseJoinColumn(name: 'intPermissionId', referencedColumnName: 'intId')]
    private Collections\Collection $permissions;

    public function __construct()
    {
        $this->permissions = new Collections\ArrayCollection();
    }

    public static function create(string $name, string $handle): self
    {
        $role = new self();
        $role->name = $name;
        $role->handle = $handle;

        return $role;
    }

    /**
     * @return Collections\Collection<int, Permission>
     */
    public function getPermissions(): Collections\Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): void
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
        }
    }

    public function removePermission(Permission $permission): void
    {
        $this->permissions->removeElement($permission);
    }

    public static function getDefault(): self
    {
        return self::create('User', self::ROLE_USER);
    }
}
