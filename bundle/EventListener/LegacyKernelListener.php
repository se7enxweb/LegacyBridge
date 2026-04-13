<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreResetLegacyKernelEvent;
use eZINI;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Resets eZINI when the Legacy Kernel is reset.
 * Resets legacy kernel handler when used in a command.
 */
class LegacyKernelListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            LegacyEvents::PRE_RESET_LEGACY_KERNEL => 'onKernelReset',
        ];
    }

    public function onKernelReset(PreResetLegacyKernelEvent $event)
    {
        $event->getLegacyKernel()->runCallback(
            static function () {
                eZINI::resetAllInstances();
            },
            true,
            false
        );
    }

}
