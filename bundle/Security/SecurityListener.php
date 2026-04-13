<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Security;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class SecurityListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        // In Ibexa 5, SecurityListener base class no longer exists.
        // This class is a no-op stub; SecurityListenerPass.process() guards with hasDefinition().
    }
}
