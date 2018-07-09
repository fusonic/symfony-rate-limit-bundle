<?php

namespace Fusonic\RateLimitBundle\Event;

use Fusonic\RateLimitBundle\Model\RouteLimitConfigInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;

class RateLimitExceededEvent extends Event
{
    /**
     * @var string
     */
    private $ip;

    /**
     * @var RouteLimitConfigInterface
     */
    private $routeLimitConfig;

    /**
     * @var GetResponseEvent
     */
    private $event;

    public function __construct(RouteLimitConfigInterface $routeLimitConfig, string $ip, GetResponseEvent $event)
    {
        $this->ip = $ip;
        $this->routeLimitConfig = $routeLimitConfig;
        $this->event = $event;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getRouteLimitConfig(): RouteLimitConfigInterface
    {
        return $this->routeLimitConfig;
    }

    public function getRequest(): Request
    {
        return $this->event->getRequest();
    }

    public function setResponse(Response $response): void
    {
        $this->event->setResponse($response);
    }
}
