<?php

declare(strict_types=1);

namespace App\Tests\Authentication\Service\Helper;

use App\Authentication\Service\Helper\RouteNameProvider;
use PHPUnit\Framework\TestCase;

class RouteNameProviderTest extends TestCase
{
    public function testReturnsCheckRouteForGithub(): void
    {
        $this->assertEquals('connect_github_check', RouteNameProvider::oAuthConnectionCheckRoute('GITHUB'));
    }

    public function testReturnsCheckRouteForGoogle(): void
    {
        $this->assertEquals('connect_google_check', RouteNameProvider::oAuthConnectionCheckRoute('GOOGLE'));
    }

    public function testIsCaseInsensitiveForProviderLookup(): void
    {
        $this->assertEquals('connect_github_check', RouteNameProvider::oAuthConnectionCheckRoute('github'));
        $this->assertEquals('connect_google_check', RouteNameProvider::oAuthConnectionCheckRoute('Google'));
    }

    public function testReturnsNullForUnknownProvider(): void
    {
        $this->assertNull(RouteNameProvider::oAuthConnectionCheckRoute('facebook'));
    }

    public function testReturnsNullForEmptyString(): void
    {
        $this->assertNull(RouteNameProvider::oAuthConnectionCheckRoute(''));
    }
}
