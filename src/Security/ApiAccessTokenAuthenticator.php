<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\HeaderAccessTokenExtractor;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class ApiAccessTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly ApiAccessTokenHandler $tokenHandler,
        private readonly HeaderAccessTokenExtractor $extractor,
    ) {}

    public function supports(Request $request): ?bool
    {
        // Only attempt auth for /api
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return false;
        }
        // Only if there's an Authorization header
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $accessToken = $this->extractor->extractAccessToken($request);
        if ($accessToken === '') {
            throw new BadCredentialsException('Empty Bearer token.');
        }
        $userBadge = $this->tokenHandler->getUserBadgeFrom($accessToken);

        // The token handler already did validation
        return new SelfValidatingPassport($userBadge);
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?JsonResponse
    {
        return null; // continue request
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(
            ['message' => 'Authentication required'],
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }
}
