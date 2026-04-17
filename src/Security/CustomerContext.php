<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RequestStack;

final class CustomerContext
{
    public function __construct(private readonly RequestStack $requestStack) {}

    public function customerId(): string
    {
        return $this->requestStack->getCurrentRequest()?->headers->get('X-Customer-Id') ?? 'demo-customer';
    }
}
