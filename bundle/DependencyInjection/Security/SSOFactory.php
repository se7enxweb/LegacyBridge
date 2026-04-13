<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Security factory for legacy SSO handlers — Symfony 7 authenticator-based.
 */
class SSOFactory implements AuthenticatorFactoryInterface
{
    public function getPriority(): int
    {
        return -10;
    }

    public function getKey(): string
    {
        return 'ezpublish_legacy_sso';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
        // No extra configuration needed.
    }

    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        $authenticatorId = 'ezpublish_legacy.security.sso_authenticator.' . $firewallName;
        $container
            ->setDefinition($authenticatorId, new ChildDefinition('ezpublish_legacy.security.sso_authenticator'))
            ->replaceArgument(0, $userProviderId);

        return $authenticatorId;
    }
}
