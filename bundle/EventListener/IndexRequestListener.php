<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use Ibexa\Bundle\Core\EventListener\IndexRequestListener as CoreIndexListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class IndexRequestListener extends CoreIndexListener
{
    /**
     * Overrides core index request, which checks if the IndexPage is configured and which page must be shown.
     * If matched SiteAccess uses legacy mode, do not execute event.
     *
     * @param RequestEvent $event
     */
    public function onKernelRequestIndex(RequestEvent $event)
    {
        if ($this->configResolver->getParameter('legacy_mode')) {
            return;
        }
        parent::onKernelRequestIndex($event);
    }
}
