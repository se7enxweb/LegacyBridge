<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Symfony\RequestStackAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * Maps the session parameters to the legacy parameters.
 */
class Session implements EventSubscriberInterface
{
    use RequestStackAware;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface|null
     */
    private $sessionStorage;

    /**
     * @var string
     */
    private $sessionStorageKey;

    public function __construct(?SessionStorageInterface $sessionStorage = null, $sessionStorageKey = null, SessionInterface $session = null)
    {
        $this->sessionStorage = $sessionStorage;
        $this->sessionStorageKey = $sessionStorageKey;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL => ['onBuildKernelHandler', 128],
        ];
    }

    /**
     * Adds the session settings to the parameters that will be injected
     * into the legacy kernel.
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent $event
     */
    public function onBuildKernelHandler(PreBuildKernelEvent $event)
    {
        $sessionInfos = [
            'configured' => false,
            'started' => false,
            'name' => false,
            'namespace' => false,
            'has_previous' => false,
            'storage' => false,
        ];

        $request = $this->getCurrentRequest();

        // In Symfony 5.3+, the session service is request-scoped and may be null when
        // injected via @?session. Additionally, PRE_BUILD_LEGACY_KERNEL fires before
        // AbstractSessionListener sets the session on the request, so $request->hasSession()
        // can also be false. Use $this->sessionStorage (always injected) directly and
        // derive name/started from PHP native session state.
        if ($this->sessionStorage !== null) {
            // Try to get a session object for name/started; fall back to PHP native functions.
            $session = $this->session ?? ($request !== null && $request->hasSession() ? $request->getSession() : null);
            $sessionInfos['configured'] = true;
            $sessionInfos['name'] = $session !== null ? $session->getName() : session_name();
            $sessionInfos['started'] = $session !== null ? $session->isStarted() : (session_status() === PHP_SESSION_ACTIVE);
            $sessionInfos['namespace'] = $this->sessionStorageKey;
            $sessionInfos['has_previous'] = $request !== null ? $request->hasPreviousSession() : false;
            $sessionInfos['storage'] = $this->sessionStorage;
        }

        $legacyKernelParameters = $event->getParameters();
        $legacyKernelParameters->set('session', $sessionInfos);

        // Deactivate session cookie settings in legacy kernel.
        // This will force using settings defined in Symfony.
        $sessionSettings = [
            'site.ini/Session/CookieTimeout' => false,
            'site.ini/Session/CookiePath' => false,
            'site.ini/Session/CookieDomain' => false,
            'site.ini/Session/CookieSecure' => false,
            'site.ini/Session/CookieHttponly' => false,
        ];
        $legacyKernelParameters->set(
            'injected-settings',
            $sessionSettings + (array)$legacyKernelParameters->get('injected-settings')
        );
    }
}
