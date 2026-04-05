<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Bridges renamed services and parameters from eZPlatform 3.x to Ibexa 4.x so
 * that legacy bridge YAML configs continue to work without modification.
 *
 * Runs before Symfony's ResolveChildDefinitionsPass and ResolveParameterPlaceHoldersPass
 * (TYPE_BEFORE_OPTIMIZATION, priority 10) so all aliases are available when they are
 * first needed.
 *
 * Service mapping (old → new FQCN):
 *   ezpublish.controller.base                 → Ibexa\Core\MVC\Symfony\Controller\Controller
 *   ezpublish.controller.content.preview.core → Ibexa\Core\MVC\Symfony\Controller\Content\PreviewController
 *   ezpublish.url_generator.base              → Ibexa\Core\MVC\Symfony\Routing\Generator
 *   ezpublish.templating.global_helper.core   → Ibexa\Core\MVC\Symfony\Templating\GlobalHelper
 *   ezpublish.security.voter.core             → Ibexa\Core\MVC\Symfony\Security\Authorization\Voter\CoreVoter
 *   ezpublish.security.voter.value_object     → Ibexa\Core\MVC\Symfony\Security\Authorization\Voter\ValueObjectVoter
 *   ezpublish.core.io.image_fieldtype.legacy_url_redecorator → Ibexa\Core\IO\UrlRedecorator
 *   ezpublish.fieldType.ezimage.io_service    → Ibexa\Core\FieldType\Image\IO\Legacy
 *   ezpublish.image_alias.imagine.alias_cleaner → Ibexa\Bundle\Core\Imagine\AliasCleaner
 *   ezpublish.urlalias_router                 → Ibexa\Bundle\Core\Routing\UrlAliasRouter
 *   ezpublish.content_preview_helper          → Ibexa\Core\Helper\ContentPreviewHelper
 *   ezpublish.persistence.connection          → ibexa.persistence.connection
 *   ezpublish.fieldType.ezpage.pageService    → ibexa.field_type.ezpage.pageService
 *   ezpublish.api.service.field_type          → ibexa.api.service.field_type
 *
 * Parameter mapping (old → new):
 *   ezpublish.image.imagemagick.enabled           → ibexa.image.imagemagick.enabled
 *   ezpublish.image.imagemagick.executable_path   → ibexa.image.imagemagick.executable_path
 *   ezpublish.image.imagemagick.executable        → ibexa.image.imagemagick.executable
 *   ezpublish.image.imagemagick.filters           → ibexa.image.imagemagick.filters
 *   ezpublish.session.attribute_bag.storage_key   → ibexa.session.attribute_bag.storage_key
 *   ezpublish.siteaccess.default                  → ibexa.site_access.default
 *   ezpublish.content_view.viewbase_layout        → ibexa.content_view.viewbase_layout
 *   ezpublish.content_view.content_block_name     → ibexa.content_view.content_block_name
 */
class ControllerBaseCompatibilityPass implements CompilerPassInterface
{
    private const SERVICE_MAP = [
        // Abstract parent services
        'ezpublish.controller.base' => 'Ibexa\Core\MVC\Symfony\Controller\Controller',
        'ezpublish.controller.content.preview.core' => 'Ibexa\Core\MVC\Symfony\Controller\Content\PreviewController',
        'ezpublish.url_generator.base' => 'Ibexa\Core\MVC\Symfony\Routing\Generator',
        'ezpublish.templating.global_helper.core' => 'Ibexa\Core\MVC\Symfony\Templating\GlobalHelper',
        // Security voter decorators
        'ezpublish.security.voter.core' => 'Ibexa\Core\MVC\Symfony\Security\Authorization\Voter\CoreVoter',
        'ezpublish.security.voter.value_object' => 'Ibexa\Core\MVC\Symfony\Security\Authorization\Voter\ValueObjectVoter',
        // Common service references used in YAML configs
        'ezpublish.config.resolver' => 'Ibexa\Bundle\Core\DependencyInjection\Configuration\ChainConfigResolver',
        'ezpublish.api.repository' => 'Ibexa\Contracts\Core\Repository\Repository',
        'ezpublish.api.service.content' => 'Ibexa\Contracts\Core\Repository\ContentService',
        'ezpublish.api.service.location' => 'Ibexa\Contracts\Core\Repository\LocationService',
        'ezpublish.api.service.user' => 'Ibexa\Contracts\Core\Repository\UserService',
        'ezpublish.siteaccess' => 'Ibexa\Core\MVC\Symfony\SiteAccess',
        'ezpublish.urlalias_generator' => 'Ibexa\Core\MVC\Symfony\Routing\Generator\UrlAliasGenerator',
        'ezpublish.api.storage_engine.legacy.connection' => 'ibexa.persistence.connection',
        'ezpublish.cache_pool' => 'ibexa.cache_pool',
        'ezpublish.spi.persistence.cache.locationHandler' => 'Ibexa\Core\Persistence\Cache\LocationHandler',
        'ezpublish.api.service.field_type' => 'ibexa.api.service.field_type',
        // IO / image services
        'ezpublish.core.io.image_fieldtype.legacy_url_redecorator' => 'Ibexa\Core\IO\UrlRedecorator',
        'ezpublish.fieldType.ezimage.io_service' => 'Ibexa\Core\FieldType\Image\IO\Legacy',
        'ezpublish.image_alias.imagine.alias_cleaner' => 'Ibexa\Bundle\Core\Imagine\AliasCleaner',
        // Routing / preview helpers
        'ezpublish.urlalias_router' => 'Ibexa\Bundle\Core\Routing\UrlAliasRouter',
        'ezpublish.content_preview_helper' => 'Ibexa\Core\Helper\ContentPreviewHelper',
        // Persistence
        'ezpublish.persistence.connection' => 'ibexa.persistence.connection',
        // HTTP cache
        'ezplatform.http_cache.purge_client' => 'ibexa.http_cache.purge_client',
        // ezpage field type (mediata-ezpage-fieldtype-bundle)
        'ezpublish.fieldType.ezpage.pageService' => 'ibexa.field_type.ezpage.pageService',
        // View services (mediata bundle)
        'ezpublish.view.configurator' => 'Ibexa\Core\MVC\Symfony\View\Configurator\ViewProvider',
        'ezpublish.view.view_parameters.injector.dispatcher' => 'Ibexa\Core\MVC\Symfony\View\ParametersInjector\EventDispatcherInjector',
    ];

    private const PARAMETER_MAP = [
        'ezpublish.image.imagemagick.enabled' => 'ibexa.image.imagemagick.enabled',
        'ezpublish.image.imagemagick.executable_path' => 'ibexa.image.imagemagick.executable_path',
        'ezpublish.image.imagemagick.executable' => 'ibexa.image.imagemagick.executable',
        'ezpublish.image.imagemagick.filters' => 'ibexa.image.imagemagick.filters',
        'ezpublish.session.attribute_bag.storage_key' => 'ibexa.session.attribute_bag.storage_key',
        'ezpublish.siteaccess.default' => 'ibexa.site_access.default',
        'ezpublish.siteaccess.relation_map' => 'ibexa.site_access.relation_map',
        'ezpublish.content_view.viewbase_layout' => 'ibexa.content_view.viewbase_layout',
        'ezpublish.content_view.content_block_name' => 'ibexa.content_view.content_block_name',
    ];

    public function process(ContainerBuilder $container): void
    {
        foreach (self::SERVICE_MAP as $legacyId => $ibexaId) {
            if ($container->hasDefinition($legacyId) || $container->hasAlias($legacyId)) {
                // Already exists, skip.
                continue;
            }

            if ($container->hasDefinition($ibexaId)) {
                // Register clone of the definition under the old legacy ID.
                $container->setDefinition($legacyId, clone $container->getDefinition($ibexaId));
            } elseif ($container->hasAlias($ibexaId)) {
                // Forward the alias if the ibexaId is itself an alias.
                $container->setAlias($legacyId, (string) $container->getAlias($ibexaId));
            } else {
                // Try as a short alias pointing to the ibexaId directly.
                // This covers ibexa.persistence.connection which may be registered only as alias.
                $container->setAlias($legacyId, $ibexaId);
            }
        }

        foreach (self::PARAMETER_MAP as $legacyParam => $ibexaParam) {
            if (
                !$container->hasParameter($legacyParam)
                && $container->hasParameter($ibexaParam)
            ) {
                $container->setParameter($legacyParam, $container->getParameter($ibexaParam));
            }
        }

        // Symfony 5.4+ with storage_factory_id no longer registers a 'session.storage' alias.
        // ezpublish_legacy.session_mapper depends on '@session.storage'; create the alias when absent.
        if (!$container->hasAlias('session.storage') && !$container->hasDefinition('session.storage')) {
            foreach (['session.storage.native', 'session.storage.php_bridge', 'session.storage.mock_file'] as $candidate) {
                if ($container->hasDefinition($candidate)) {
                    $container->setAlias('session.storage', $candidate)->setPublic(false);
                    break;
                }
            }
        }
    }
}
