<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\Routing;

use eZ\Bundle\EzPublishLegacyBundle\Routing\FallbackRouter;
use eZ\Bundle\EzPublishLegacyBundle\Routing\UrlGenerator;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class FallbackRouterTest extends TestCase
{
    private const GROUPS = [
        'site_group' => ['site', 'legacy_site', 'legacy_admin'],
        'admin_group' => ['admin'],
    ];

    private function createRouter(array $legacyMode = []): FallbackRouter
    {
        $router = new FallbackRouter($this->createStub(UrlGenerator::class));
        $router->setPlatformAdminSiteAccessGroups(self::GROUPS, ['admin_group']);

        $configResolver = $this->createStub(ConfigResolverInterface::class);
        $configResolver->method('hasParameter')->willReturnCallback(
            static function ($name, $namespace = null, $scope = null) use ($legacyMode) {
                return $name === 'legacy_mode' && isset($legacyMode[$scope]);
            }
        );
        $configResolver->method('getParameter')->willReturnCallback(
            static function ($name, $namespace = null, $scope = null) use ($legacyMode) {
                return $legacyMode[$scope] ?? false;
            }
        );
        $router->setConfigResolver($configResolver);

        return $router;
    }

    private function createRequest(string $siteAccessName, string $semanticPathinfo): Request
    {
        $request = Request::create('/' . $siteAccessName . $semanticPathinfo);
        $request->attributes->set('siteaccess', new SiteAccess($siteAccessName));
        $request->attributes->set('semanticPathinfo', $semanticPathinfo);

        return $request;
    }

    public function testUnknownPathOfPlatformAdminSiteAccessIsNotFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $this->createRouter()->matchRequest($this->createRequest('admin', '/user/list'));
    }

    public function testPlatformAdminSiteAccessInLegacyModeFallsBackToLegacy(): void
    {
        $result = $this->createRouter(['admin' => true])
            ->matchRequest($this->createRequest('admin', '/user/list'));

        self::assertSame(FallbackRouter::ROUTE_NAME, $result['_route']);
    }

    /**
     * @dataProvider legacySiteAccessProvider
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('legacySiteAccessProvider')]
    public function testOtherSiteAccessesFallBackToLegacy(string $siteAccessName, string $path): void
    {
        $result = $this->createRouter(['legacy_site' => true, 'legacy_admin' => true])
            ->matchRequest($this->createRequest($siteAccessName, $path));

        self::assertSame(FallbackRouter::ROUTE_NAME, $result['_route']);
        self::assertSame('ezpublish_legacy.controller::indexAction', $result['_controller']);
        self::assertSame($path . '?', $result['module_uri']);
    }

    public static function legacySiteAccessProvider(): array
    {
        return [
            'legacy admin' => ['legacy_admin', '/user/login'],
            'legacy site' => ['legacy_site', '/content/view/full/2'],
            'site front end' => ['site', '/ezinfo/about'],
        ];
    }

    public function testRequestWithoutSiteAccessFallsBackToLegacy(): void
    {
        $request = Request::create('/user/login');

        $result = $this->createRouter()->matchRequest($request);

        self::assertSame(FallbackRouter::ROUTE_NAME, $result['_route']);
    }
}
