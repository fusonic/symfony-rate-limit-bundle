<?php

namespace Fusonic\RateLimitBundle\Event;

use Symfony\Component\EventDispatcher\Event;

final class RateLimitEvents extends Event
{
    // Emitted
    public const ROUTE_LIMIT_EXCEEDED = 'fusonic_rate_limit.route_limit_exceeded';
    public const ROUTE_ATTEMPTS_UPDATED = 'fusonic_rate_limit.route_attempts_updated';

    // Handled
    public const ROUTE_RESET_ATTEMPTS = 'fusonic_rate_limit.route_reset_attempts';

    private function __construct()
    {
    }
}
