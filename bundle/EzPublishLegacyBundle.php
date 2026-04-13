<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle;

use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\ControllerBaseCompatibilityPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\PageServicePass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\RememberMeListenerPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\LegacyBundlesPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\LegacySessionPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\RelatedSiteAccessesCleanupPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\RequestIndexListenerPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\RoutingPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\SecurityListenerPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Security\SSOFactory;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\LegacyPass;
use eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler\TwigPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
class EzPublishLegacyBundle extends Bundle
{
    public function boot()
    {
        if (!$this->container->getParameter('ezpublish_legacy.enabled')) {
            return;
        }

        $autoload = $this->container->getParameter('ezpublish_legacy.root_dir') . '/autoload.php';
        if (!is_file($autoload)) {
            return;
        }

        // Deactivate eZComponents loading from legacy autoload.php as they are already loaded
        if (!\defined('EZCBASE_ENABLED')) {
            \define('EZCBASE_ENABLED', false);
        }

        require_once $autoload;
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        // Must run before Symfony's ResolveChildDefinitionsPass so ezpublish.controller.base
        // exists when child services are resolved on Ibexa 4.x.
        $container->addCompilerPass(new ControllerBaseCompatibilityPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
        $container->addCompilerPass(new RelatedSiteAccessesCleanupPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new LegacyPass());
        $container->addCompilerPass(new TwigPass());
        $container->addCompilerPass(new LegacyBundlesPass());
        $container->addCompilerPass(new RoutingPass());
        $container->addCompilerPass(new LegacySessionPass());
        $container->addCompilerPass(new RememberMeListenerPass());
        $container->addCompilerPass(new PageServicePass());
        $container->addCompilerPass(new RequestIndexListenerPass());
        $container->addCompilerPass(new SecurityListenerPass());

        /** @var \Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension $securityExtension */
        $securityExtension = $container->getExtension('security');
        $securityExtension->addAuthenticatorFactory(new SSOFactory());
    }
}
