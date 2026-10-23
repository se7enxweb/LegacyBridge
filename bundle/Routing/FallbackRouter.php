<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Psr\Log\LoggerInterface;
use RuntimeException;

class FallbackRouter implements RouterInterface, RequestMatcherInterface
{
    const ROUTE_NAME = 'ez_legacy';

    /**
     * @var \Symfony\Component\Routing\RequestContext
     */
    private $context;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * Names of the Platform admin siteaccesses, as keys.
     *
     * @var array<string, true>
     */
    private $platformAdminSiteAccesses = [];

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface|null
     */
    private $configResolver;

    public function __construct(UrlGenerator $urlGenerator, RequestContext $context = null, LoggerInterface $logger = null)
    {
        $this->urlGenerator = $urlGenerator;
        $this->context = $context = $context ?: new RequestContext();
        $this->logger = $logger;
    }

    /**
     * Sets the request context.
     *
     * @param \Symfony\Component\Routing\RequestContext $context The context
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * Gets the request context.
     *
     * @return \Symfony\Component\Routing\RequestContext The context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Gets the RouteCollection instance associated with this Router.
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function getRouteCollection()
    {
        // No route registered for legacy fallback, request will be forwarded directly to the legacy kernel
        return new RouteCollection();
    }

    /**
     * Generates a URL for an eZ Publish legacy fallback route, from the given parameters.
     * "module_uri" must be provided as a key in $parameters. The module URI must contain ordered parameters if any
     * (e.g. /content/view/full/2, "full", and "2" being regular ordered parameters. See your module definition for more info.).
     * All additional named parameters will be passed as unordered params in the form "/(<paramName>)/<paramValue".
     *
     * Example :
     * <code>
     * $params = array(
     *     'module_uri'    => '/content/view/full/2',
     *     'offset'        => 30,
     *     'limit'         => 10
     * );
     * $url = $legacyRouter->generate( 'ez_legacy', $params );
     * // $url will be "/content/view/full/2/(offset)/30/(limit)/10"
     * </code>
     *
     * @param string $name The name of the route
     * @param mixed $parameters An array of parameters
     * @param int $referenceType The type of reference to be generated (one of the constants)
     *
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @throws \InvalidArgumentException
     *
     * @return string The generated URL
     *
     * @api
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        if ($name === self::ROUTE_NAME) {
            if (!isset($parameters['module_uri'])) {
                throw new \InvalidArgumentException('When generating an eZ Publish legacy fallback route, "uri" parameter must be provided.');
            }

            $moduleUri = $parameters['module_uri'];
            unset($parameters['module_uri']);

            return $this->urlGenerator->generate($moduleUri, $parameters, $referenceType);
        }

        throw new RouteNotFoundException();
    }

    public function match($pathinfo)
    {
        throw new RuntimeException("The UrlAliasRouter doesn't support the match() method. Please use matchRequest() instead.");
    }

    /**
     * Sets the siteaccess groups (the "ibexa.site_access.groups" parameter) and the names of the groups whose
     * siteaccesses belong to the Platform Admin UI. The legacy fallback does not serve those siteaccesses.
     *
     * @param array<string, string[]> $siteAccessGroups
     * @param string[] $platformAdminGroups
     */
    public function setPlatformAdminSiteAccessGroups(array $siteAccessGroups, array $platformAdminGroups): void
    {
        $this->platformAdminSiteAccesses = [];
        foreach ($platformAdminGroups as $groupName) {
            foreach ($siteAccessGroups[$groupName] ?? [] as $siteAccessName) {
                $this->platformAdminSiteAccesses[$siteAccessName] = true;
            }
        }
    }

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver (untyped: the 4.6 kernel names it Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface)
     */
    public function setConfigResolver($configResolver): void
    {
        $this->configResolver = $configResolver;
    }

    public function matchRequest(Request $request)
    {
        $siteAccess = $request->attributes->get('siteaccess');
        $siteAccessName = is_object($siteAccess) && isset($siteAccess->name) ? (string)$siteAccess->name : null;
        if ($siteAccessName !== null && $this->isPlatformAdminSiteAccess($siteAccessName)) {
            // A path in the Platform admin siteaccess that no Platform route matches does not exist. Handing it to
            // the legacy kernel would render a legacy page (the legacy login, with HTTP 200) for a siteaccess the
            // legacy kernel has no settings for.
            throw new ResourceNotFoundException(sprintf(
                'No route found for "%s" in the Platform admin siteaccess "%s"; the legacy fallback does not serve it.',
                $request->getPathInfo(),
                $siteAccessName
            ));
        }

        $moduleUri = rtrim($request->attributes->get('semanticPathinfo', $request->getPathInfo()), '/')
                     . $request->attributes->get('viewParametersString', '')
                     . '?' . $request->getQueryString();

        return [
            '_route' => self::ROUTE_NAME,
            '_controller' => 'ezpublish_legacy.controller:indexAction',
            'module_uri' => $moduleUri,
        ];
    }

    /**
     * A siteaccess of the Platform admin groups, unless it runs in legacy mode (then the legacy kernel serves it).
     */
    private function isPlatformAdminSiteAccess(string $siteAccessName): bool
    {
        if (!isset($this->platformAdminSiteAccesses[$siteAccessName])) {
            return false;
        }

        if ($this->configResolver !== null
            && $this->configResolver->hasParameter('legacy_mode', null, $siteAccessName)
            && $this->configResolver->getParameter('legacy_mode', null, $siteAccessName) === true
        ) {
            return false;
        }

        return true;
    }
}
