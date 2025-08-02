<?php

declare(strict_types=1);

// region Init

namespace App\Security;

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

// endregion

class JwtAuthenticator extends AbstractAuthenticator
{
    // region Declarations
    public function __construct(
        private readonly string $auth0Domain, // Inlined property promotion
        private readonly string $auth0ClientId,
        private readonly string $auth0ClientSecret,
        private readonly string $auth0Audience,
        private readonly UserProvider $userProvider, // Inject UserProvider
    ) {
    }
    // endregion

    // region supports
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization')
            && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }
    // endregion

    // region authenticate
    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');
        $jwt = substr($authHeader, 7);

        try {
            $config = new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_API,
                domain: $this->auth0Domain,
                clientId: $this->auth0ClientId,
                clientSecret: $this->auth0ClientSecret,
                audience: [$this->auth0Audience],
                tokenAlgorithm: 'RS256'
            );

            $auth0 = new Auth0($config);
            $token = $auth0->decode($jwt);
            $ssoId = $token->toArray()['sub'];

            $user = $this->userProvider->loadUserByIdentifier($ssoId);

            return new SelfValidatingPassport(new UserBadge($ssoId, function () use ($user) {
                return $user;
            }));
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Invalid JWT Token');
        }
    }
    // endregion

    // region onAuthenticationSuccess
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }
    // endregion

    // region onAuthenticationFailure
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }
    // endregion
}
