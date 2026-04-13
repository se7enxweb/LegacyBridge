<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Cache;

use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;

/**
 * A PurgeClient decorator that allows the actual purger to be switched on/off.
 */
class SwitchableHttpCachePurger implements PurgeClientInterface
{
    use Switchable;

    /**
     * @var \EzSystems\PlatformHttpCacheBundle\PurgeClient\PurgeClientInterface
     */
    private $purgeClient;

    public function __construct(PurgeClientInterface $purgeClient)
    {
        $this->purgeClient = $purgeClient;
    }

    public function purge(array $tags): void
    {
        if ($this->isSwitchedOff()) {
            return;
        }

        $this->purgeClient->purge($tags);
    }

    public function purgeAll(): void
    {
        if ($this->isSwitchedOff()) {
            return;
        }

        $this->purgeClient->purgeAll();
    }

    /**
     * Implemented for BC with deprecated PurgeClientInterface::purgeForContent from eZ kernel.
     *
     * @param int $contentId
     * @param array $locationIds
     */
    public function purgeForContent($contentId, $locationIds = [])
    {
        if ($this->isSwitchedOff() || !method_exists($this->purgeClient, 'purgeForContent')) {
            return;
        }

        $this->purgeClient->purgeForContent($contentId, $locationIds);
    }
}
