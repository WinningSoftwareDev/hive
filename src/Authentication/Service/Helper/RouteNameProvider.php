<?php

declare(strict_types=1);

namespace App\Authentication\Service\Helper;

use App\Core\Entity\OauthProvider;

class RouteNameProvider
{
    public static function oAuthConnectionCheckRoute(string $providerHandle): ?string
    {
        if (!in_array(strtoupper($providerHandle), OauthProvider::PROVIDERS, true)) {
            return null;
        }

        return 'connect_' . strtolower($providerHandle) . '_check';
    }
}
