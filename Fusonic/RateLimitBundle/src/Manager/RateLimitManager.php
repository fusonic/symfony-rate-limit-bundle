<?php

namespace Fusonic\RateLimitBundle\Manager;

use Doctrine\Common\Cache\CacheProvider;
use Fusonic\RateLimitBundle\Event\RateLimitEvents;
use Fusonic\RateLimitBundle\Event\RateLimitExceededEvent;
use Fusonic\RateLimitBundle\Event\RateLimitAttemptsUpdatedEvent;
use Fusonic\RateLimitBundle\Model\RouteLimitConfig;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RateLimitManager implements RateLimitManagerInterface
{
    /**
     * @var array
     */
    private $rateLimitConfig;

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(
        array $rateLimitConfig,
        CacheProvider $cacheProvider,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->rateLimitConfig = $rateLimitConfig;
        $this->cacheProvider = $cacheProvider;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function resetAttemptsForIpAndRoute(string $ip, string $route): void
    {
        if ($this->isRateLimitEnabled() && $this->isRouteRateLimited($route)) {
            $cacheId = $this->generateCacheId($ip, $route);
            $this->cacheProvider->delete($cacheId);
        }
    }

    public function handleRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        $ip = $request->getClientIp();

        if (!$this->isRateLimitEnabled() || !$this->isRouteRateLimited($route)) {
            return;
        }

        $routeConfig = RouteLimitConfig::fromRouteConfig($route, $this->getRouteConfig($route));
        $cacheId = $this->generateCacheId($ip, $route);

        $attempts = $this->cacheProvider->fetch($cacheId) ?: 0;
        $this->cacheProvider->save($cacheId, ++$attempts, $routeConfig->getPeriod());

        if ($attempts > $routeConfig->getLimit()) {
            $this->logger->critical(
                'Too many attempts ('.$attempts.'/.'.$routeConfig->getLimit().') by: '.$ip.' on route: '.$route
            );

            $exceededEvent = new RateLimitExceededEvent($routeConfig, $ip, $event);
            $this->dispatcher->dispatch(RateLimitEvents::ROUTE_LIMIT_EXCEEDED, $exceededEvent);

            return;
        }

        $this->logger->info(
            'Route accessed ('.$attempts.'/.'.$routeConfig->getLimit().') by: '.$ip.' on route: '.$route
        );

        $updateEvent = new RateLimitAttemptsUpdatedEvent($routeConfig, $ip, $event);
        $this->dispatcher->dispatch(RateLimitEvents::ROUTE_ATTEMPTS_UPDATED, $updateEvent);
    }

    protected function isRateLimitEnabled(): bool
    {
        return isset($this->rateLimitConfig['enabled']) && true === $this->rateLimitConfig['enabled'];
    }

    protected function isRouteRateLimited($route): bool
    {
        return array_key_exists($route, $this->rateLimitConfig['routes']) &&
            isset($this->rateLimitConfig['routes'][$route]);
    }

    protected function getRouteConfig(string $route): array
    {
        return $this->rateLimitConfig['routes'][$route];
    }

    protected function generateCacheId(string $ip, string $route): string
    {
        return sha1($ip.$route);
    }
}
