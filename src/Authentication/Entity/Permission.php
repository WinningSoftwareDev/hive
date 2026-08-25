<?php

declare(strict_types=1);

namespace App\Authentication\Entity;

use App\Core\Entity\Application\AbstractLookupEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ublPermission', schema: 'Authentication')]
class Permission extends AbstractLookupEntity
{
}
