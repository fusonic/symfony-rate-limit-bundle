<?php

namespace Fusonic\RateLimitBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class RateLimitEvents extends Event
{
    // Emitted
    public const ROUTE_LIMIT_EXCEEDED = RateLimitExceededEvent::class;
    public const ROUTE_ATTEMPTS_UPDATED = RateLimitAttemptsUpdatedEvent::class;

    // Handled
    public const ROUTE_RESET_ATTEMPTS = RateLimitResetAttemptsEvent::class;

    private function __construct()
    {
    }
}
