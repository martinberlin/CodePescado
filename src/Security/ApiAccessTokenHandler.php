<?php
namespace App\Security;

use App\Repository\ApiClientRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Reference https://symfony.com/doc/current/security/access_token.html
 */
final class ApiAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly ApiClientRepository $apiClientRepository,
    ) {}

    /**
     * Alternatively ApiClientRepository could be also added here but looks nicer in constructor
     * @param string $accessToken
     * @return UserBadge
     */
    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $client = $this->apiClientRepository->findOneBy(['apiKey' => $accessToken]);

        if (null === $client) {
            throw new BadCredentialsException('API token not found');
        }
        if (!$client->isActive()) {
            throw new BadCredentialsException('API client not active.');
        }

        $name = $client->getName();
        if ($name === '') {
            throw new BadCredentialsException('API client has no name.');
        }
        // This closure returns the UserInterface directly.
        return new UserBadge($name, fn() => new ApiClientUser($name));
    }

}
