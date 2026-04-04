<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to cleanup related siteaccess, i.e. remove from relation map those in legacy mode.
 */
class RelatedSiteAccessesCleanupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // Support both Ibexa 4.x (ibexa.*) and eZPlatform 3.x (ezpublish.*) parameter names.
        $resolverService = $container->hasAlias('ibexa.config.resolver') || $container->hasDefinition('ibexa.config.resolver')
            ? 'ibexa.config.resolver'
            : 'ezpublish.config.resolver';
        $configResolver = $container->get($resolverService);

        $relationMapParam = $container->hasParameter('ibexa.site_access.relation_map')
            ? 'ibexa.site_access.relation_map'
            : 'ezpublish.siteaccess.relation_map';
        $relationMap = $container->getParameter($relationMapParam);

        $configNs = $container->hasParameter('ibexa.site_access.relation_map')
            ? 'ibexa.site_access.config'
            : 'ezsettings';

        // Exclude siteaccesses in legacy_mode (e.g. admin interface)
        foreach ($relationMap as $repository => &$saByRootLocation) {
            foreach ($saByRootLocation as $rootLocation => $saList) {
                foreach ($saList as $i => $sa) {
                    try {
                        $legacyMode = $configResolver->getParameter('legacy_mode', $configNs, $sa);
                    } catch (\Exception $e) {
                        $legacyMode = false;
                    }
                    if ($legacyMode === true) {
                        unset($saByRootLocation[$rootLocation][$i]);
                    }
                }
            }
        }
        $container->setParameter($relationMapParam, $relationMap);

        $saListParam = $container->hasParameter('ibexa.site_access.list')
            ? 'ibexa.site_access.list'
            : 'ezpublish.siteaccess.list';
        $saList = $container->getParameter($saListParam);

        foreach ($saList as $sa) {
            try {
                $saLegacyMode = $configResolver->getParameter('legacy_mode', $configNs, $sa);
            } catch (\Exception $e) {
                $saLegacyMode = false;
            }
            if ($saLegacyMode === true) {
                continue;
            }

            try {
                $relatedSAs = $configResolver->getParameter('related_siteaccesses', $configNs, $sa);
            } catch (\Exception $e) {
                $relatedSAs = [];
            }
            foreach ($relatedSAs as $i => $relatedSa) {
                try {
                    $relatedLegacyMode = $configResolver->getParameter('legacy_mode', $configNs, $relatedSa);
                } catch (\Exception $e) {
                    $relatedLegacyMode = false;
                }
                if ($relatedLegacyMode === true) {
                    unset($relatedSAs[$i]);
                }
            }
            if ($container->hasParameter('ibexa.site_access.relation_map')) {
                $container->setParameter("ibexa.site_access.config.$sa.related_siteaccesses", $relatedSAs);
            } else {
                $container->setParameter("ezsettings.$sa.related_siteaccesses", $relatedSAs);
            }
        }
    }
}
