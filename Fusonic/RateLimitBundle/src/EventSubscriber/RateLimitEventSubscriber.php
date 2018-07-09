<?php

namespace Fusonic\RateLimitBundle\EventSubscriber;

use Fusonic\RateLimitBundle\Event\RateLimitEvents;
use Fusonic\RateLimitBundle\Event\RateLimitResetAttemptsEvent;
use Fusonic\RateLimitBundle\Manager\RateLimitManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RateLimitEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var RateLimitManagerInterface
     */
    protected $rateLimitManager;

    public function __construct(RateLimitManagerInterface $rateLimitManager)
    {
        $this->rateLimitManager = $rateLimitManager;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if ($event->isMasterRequest()) {
            $this->rateLimitManager->handleRequest($event);
        }
    }

    public function onRateLimitReset(RateLimitResetAttemptsEvent $event): void
    {
        $this->rateLimitManager->resetAttemptsForIpAndRoute($event->getIp(), $event->getRoute());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 17],
            ],
            RateLimitEvents::ROUTE_RESET_ATTEMPTS => ['onRateLimitReset'],
        ];
    }
}
