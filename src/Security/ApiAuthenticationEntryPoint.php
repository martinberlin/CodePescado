<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Ensures unauthenticated API requests return JSON (not HTML).
 * Triggered when authentication is required but no authenticator produced a response.
 */
final class ApiAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): JsonResponse
    {
        // Keep message generic; don't leak details about auth internals.
        return new JsonResponse(
            ['message' => 'Authentication required'],
            JsonResponse::HTTP_UNAUTHORIZED
        );
    }
}
