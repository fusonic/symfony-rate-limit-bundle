<?php

namespace Fusonic\RateLimitBundle\Event;

use Fusonic\RateLimitBundle\Model\RouteLimitConfigInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\EventDispatcher\Event;

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
     * @var RequestEvent
     */
    private $event;

    public function __construct(RouteLimitConfigInterface $routeLimitConfig, string $ip, RequestEvent $event)
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
