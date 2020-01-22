<?php

namespace Fusonic\RateLimitBundle\Manager;

use Symfony\Component\HttpKernel\Event\RequestEvent;

interface RateLimitManagerInterface
{
    public function resetAttemptsForIpAndRoute(string $ip, string $route): void;

    public function handleRequest(RequestEvent $event): void;
}
