<?php

namespace Fusonic\RateLimitBundle\Tests\EventSubscriber;

use Fusonic\RateLimitBundle\Event\RateLimitResetAttemptsEvent;
use Fusonic\RateLimitBundle\Tests\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RateLimitEventSubscriberTest extends TestCase
{
    public function testResetRateLimitAttemptsEventHandling(): void
    {
        $route = 'foo';
        $ip = '1.1.1.1';
        $id = sha1($ip.$route);

        /** @var  $cache */
        $cache = $this->getCache();
        $this->createItem($id);
        $this->assertTrue($cache->getItem($id)->isHit());
        $this->dispatchResetAttemptsEvent($route, $ip);

        $this->assertFalse($cache->getItem($id)->isHit());
    }

    public function testRateLimitAttemptsUpdateEventHandling(): void
    {
        $route = 'foo';
        $ip = '127.0.0.1';
        $id = sha1($ip.$route);

        $request = Request::create('/foo', 'GET', ['_route' => $route]);
        $cache = $this->getCache();
        $this->assertFalse($cache->getItem($id)->isHit());

        $this->dispatchRequestEvent($request);

        $this->assertTrue($cache->getItem($id)->isHit());
        $cacheEntry = $cache->getItem($id);
        $this->assertEquals(1, $cacheEntry->get());

        $this->dispatchRequestEvent($request);

        $this->assertTrue($cache->getItem($id)->isHit());
        $cacheEntry = $cache->getItem($id);
        $this->assertEquals(2, $cacheEntry->get());

        $this->dispatchRequestEvent($request);

        $this->assertTrue($cache->getItem($id)->isHit());
        $cacheEntry = $cache->getItem($id);
        $this->assertEquals(3, $cacheEntry->get());
    }

    private function dispatchResetAttemptsEvent($route, $ip): void
    {
        /** @var EventDispatcherInterface $dispatch */
        $dispatch = $this->container->get('event_dispatcher');
        $dispatch->dispatch(new RateLimitResetAttemptsEvent($route, $ip));
    }

    private function dispatchRequestEvent(Request $request): void
    {
        /** @var EventDispatcherInterface $dispatch */
        $dispatch = $this->container->get('event_dispatcher');
        $dispatch->dispatch(
            new RequestEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST),
            KernelEvents::REQUEST
        );
    }

    private function getCache(): CacheItemPoolInterface
    {
        /** @var CacheItemPoolInterface $cache */
        $cache = $this->container->get('fusonic_rate_limit.cache_provider');

        return $cache;
    }

    private function createItem(string $id, int $value = 5, int $ttl = 3600): CacheItemInterface
    {
        $cache = $this->getCache();

        $item = $cache->getItem($id);
        $item->set($value);
        $item->expiresAfter($ttl);
        $cache->save($item);

        return $item;
    }
}
