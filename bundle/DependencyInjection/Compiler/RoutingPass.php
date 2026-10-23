<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishLegacyBundle\Routing\DefaultRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Pass modifying the default router to inject legacy aware routes, and telling the legacy fallback router which
 * siteaccesses belong to the Platform Admin UI.
 */
class RoutingPass implements CompilerPassInterface
{
    /**
     * Container parameters that hold the siteaccess groups, newest name first.
     */
    private const SITEACCESS_GROUPS_PARAMETERS = ['ibexa.site_access.groups', 'ezpublish.siteaccess.groups'];

    public function process(ContainerBuilder $container)
    {
        $this->configureFallbackRouter($container);

        if (!$container->hasDefinition('router.default')) {
            return;
        }

        $container->getDefinition('router.default')
            ->setClass(DefaultRouter::class)
            ->addMethodCall(
                'setLegacyAwareRoutes',
                ['%ezpublish.default_router.legacy_aware_routes%']
            );

        if ($container->hasDefinition('ezpublish_rest.templated_router')) {
            $container->getDefinition('ezpublish_rest.templated_router')
                ->setClass(DefaultRouter::class);
        }
    }

    private function configureFallbackRouter(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ezpublish_legacy.router')
            || !$container->hasParameter('ezpublish_legacy.fallback_router.platform_admin_groups')
        ) {
            return;
        }

        $siteAccessGroups = [];
        foreach (self::SITEACCESS_GROUPS_PARAMETERS as $parameter) {
            if ($container->hasParameter($parameter)) {
                $siteAccessGroups = (array)$container->getParameter($parameter);
                break;
            }
        }

        $container->getDefinition('ezpublish_legacy.router')
            ->addMethodCall(
                'setPlatformAdminSiteAccessGroups',
                [
                    $siteAccessGroups,
                    (array)$container->getParameter('ezpublish_legacy.fallback_router.platform_admin_groups'),
                ]
            );
    }
}
