<?php

declare(strict_types=1);

namespace App\Core\Entity;

use App\Core\Entity\Application\AbstractLookupEntity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'ublEmailType', schema: 'Core')]
class EmailType extends AbstractLookupEntity
{
    public const string PASSWORD_RESET = 'PASSWORD_RESET';
    public const string VERIFY_EMAIL_ADDRESS = 'VERIFY_EMAIL_ADDRESS';

    #[ORM\Column(name: 'strTemplate', type: 'string')]
    protected string $template;

    public function getTemplate(): string
    {
        return $this->template;
    }
}
