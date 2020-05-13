<?php

namespace Fusonic\RateLimitBundle\Manager;

use Fusonic\RateLimitBundle\Event\RateLimitAttemptsUpdatedEvent;
use Fusonic\RateLimitBundle\Event\RateLimitExceededEvent;
use Fusonic\RateLimitBundle\Model\RouteLimitConfig;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RateLimitManager implements RateLimitManagerInterface
{
    /**
     * @var array
     */
    private $rateLimitConfig;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

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
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->rateLimitConfig = $rateLimitConfig;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function resetAttemptsForIpAndRoute(string $ip, string $route): void
    {
        if ($this->isRateLimitEnabled() && $this->isRouteRateLimited($route)) {
            $key = $this->generateCacheKey($ip, $route);
            $this->cache->deleteItem($key);
        }
    }

    public function handleRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        $ip = $request->getClientIp();

        if (!$this->isRateLimitEnabled() || !$this->isRouteRateLimited($route)) {
            return;
        }

        $routeConfig = RouteLimitConfig::fromRouteConfig($route, $this->getRouteConfig($route));
        $key = $this->generateCacheKey($ip, $route);

        $attempts = 1;
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            $attempts = $item->get() + 1;
        }

        $item->set($attempts);
        $item->expiresAfter($routeConfig->getPeriod());
        $this->cache->save($item);

        if ($attempts > $routeConfig->getLimit()) {
            $this->logger->critical(
                'Too many attempts ('.$attempts.'/.'.$routeConfig->getLimit().') by: '.$ip.' on route: '.$route
            );

            $exceededEvent = new RateLimitExceededEvent($routeConfig, $ip, $event);
            $this->dispatcher->dispatch($exceededEvent);

            return;
        }

        $this->logger->info(
            'Route accessed ('.$attempts.'/.'.$routeConfig->getLimit().') by: '.$ip.' on route: '.$route
        );

        $updateEvent = new RateLimitAttemptsUpdatedEvent($routeConfig, $ip, $event);
        $this->dispatcher->dispatch($updateEvent);
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

    protected function generateCacheKey(string $ip, string $route): string
    {
        return sha1($ip.$route);
    }
}
