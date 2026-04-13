<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Security;

use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Core\MVC\Symfony\Security\User;
use eZINI;
use eZUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Symfony 7 authenticator for legacy SSO handlers.
 */
class SSOAuthenticator extends AbstractAuthenticator
{
    /** @var UserProviderInterface */
    private $userProvider;

    /** @var \Closure */
    private $legacyKernelClosure;

    /** @var UserService */
    private $userService;

    /** @var LoggerInterface|null */
    private $logger;

    public function __construct(
        UserProviderInterface $userProvider,
        \Closure $legacyKernelClosure,
        UserService $userService,
        ?LoggerInterface $logger = null
    ) {
        $this->userProvider = $userProvider;
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->userService = $userService;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        // Run lazily — let authenticate() decide.
        return null;
    }

    public function authenticate(Request $request): Passport
    {
        $kernelClosure = $this->legacyKernelClosure;
        /** @var \ezpKernelHandler $legacyKernel */
        $legacyKernel = $kernelClosure();
        $logger = $this->logger;

        $legacyUser = $legacyKernel->runCallback(
            static function () use ($logger) {
                foreach (eZINI::instance()->variable('UserSettings', 'SingleSignOnHandlerArray') as $ssoHandlerName) {
                    $className = 'eZ' . $ssoHandlerName . 'SSOHandler';
                    if (!class_exists($className)) {
                        if ($logger) {
                            $logger->error("Undefined legacy SSOHandler class: $className");
                        }
                        continue;
                    }

                    $ssoHandler = new $className();
                    $ssoUser = $ssoHandler->handleSSOLogin();
                    if (!$ssoUser instanceof eZUser) {
                        continue;
                    }

                    if ($logger) {
                        $logger->info("Matched user using eZ legacy SSO Handler: $className");
                    }

                    return $ssoUser;
                }

                return null;
            },
            false,
            false
        );

        if (!$legacyUser instanceof eZUser) {
            throw new AuthenticationException('No legacy SSO user matched.');
        }

        $contentObjectId = $legacyUser->attribute('contentobject_id');
        $userService = $this->userService;

        return new SelfValidatingPassport(
            new UserBadge(
                (string) $contentObjectId,
                function (string $identifier) use ($userService): User {
                    try {
                        return new User(
                            $userService->loadUser((int) $identifier),
                            ['ROLE_USER']
                        );
                    } catch (\Exception $e) {
                        throw new UserNotFoundException("Legacy SSO user $identifier not found.", 0, $e);
                    }
                }
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Continue the request — the user is now authenticated.
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Let the request continue unauthenticated.
        return null;
    }
}
