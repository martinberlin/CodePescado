<?php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * “No user at all” isn’t really a supported state for IS_AUTHENTICATED_* rules
 * Minimal ApiClientUser
 */
final class ApiClientUser implements UserInterface
{
    public function __construct(private readonly string $clientName) {}

    public function getUserIdentifier(): string
    {
        // token is associated with client name as identifier
        return $this->clientName;
    }

    public function getRoles(): array
    {
        return ['ROLE_API'];
    }

    public function eraseCredentials(): void {}
}
