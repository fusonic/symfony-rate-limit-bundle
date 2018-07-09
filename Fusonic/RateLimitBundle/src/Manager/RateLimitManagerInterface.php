<?php

namespace Fusonic\RateLimitBundle\Manager;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

interface RateLimitManagerInterface
{
    public function resetAttemptsForIpAndRoute(string $ip, string $route): void;

    public function handleRequest(GetResponseEvent $event): void;
}
