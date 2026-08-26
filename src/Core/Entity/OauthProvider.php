<?php

declare(strict_types=1);

namespace App\Core\Entity;

use App\Core\Entity\Application\AbstractLookupEntity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'ublOauthProvider', schema: 'Core')]
class OauthProvider extends AbstractLookupEntity
{
    public const string GITHUB = 'GITHUB';
    public const string GOOGLE = 'GOOGLE';

    /**
     * @var string[]
     */
    public const array PROVIDERS = [
        self::GITHUB,
        self::GOOGLE,
    ];

    #[Column(name: 'strIcon', type: 'string', length: 100, nullable: true)]
    protected ?string $icon = null;

    public function getIcon(): ?string
    {
        return $this->icon;
    }
}
