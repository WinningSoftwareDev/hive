<?php

declare(strict_types=1);

namespace App\Core\Entity\Application;

use Doctrine\ORM\Mapping as ORM;

class AbstractLookupEntity extends AbstractBaseEntity
{
    #[ORM\Column(name: 'strName', type: 'string')]
    protected string $name;

    #[ORM\Column(name: 'strHandle', type: 'string')]
    protected string $handle;

    #[ORM\Column(name: 'bolActive', type: 'boolean')]
    protected bool $active = true;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function setHandle(string $handle): void
    {
        $this->handle = $handle;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }
}
