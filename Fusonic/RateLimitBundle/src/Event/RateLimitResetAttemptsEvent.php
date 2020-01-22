<?php

namespace Fusonic\RateLimitBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class RateLimitResetAttemptsEvent extends Event
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @var string
     */
    private $route;

    public function __construct(string $route, string $ip)
    {
        $this->ip = $ip;
        $this->route = $route;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getRoute(): string
    {
        return $this->route;
    }
}
