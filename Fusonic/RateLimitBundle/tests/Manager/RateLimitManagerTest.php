<?php

namespace Fusonic\RateLimitBundle\Tests\Manager;

use Fusonic\RateLimitBundle\Tests\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RateLimitManagerTest extends TestCase
{
    public function testResetRateLimitForRoute(): void
    {
        $route = 'foo';
        $ip = '1.1.1.1';
        $id = sha1($ip.$route);

        $cache = $this->getCache();
        $this->createItem($id);
        $this->assertTrue($cache->getItem($id)->isHit());

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->resetAttemptsForIpAndRoute($ip, 'foo');
        $this->assertFalse($cache->getItem($id)->isHit());
    }

    public function testResetRateLimitForNotExistingRoute(): void
    {
        $route = 'foo';
        $ip = '1.1.1.1';
        $id = sha1($ip.$route);

        $cache = $this->getCache();
        $this->createItem($id);
        $this->assertTrue($cache->getItem($id)->isHit());

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->resetAttemptsForIpAndRoute($ip, 'foo2');
        $this->assertTrue($cache->getItem($id)->isHit());
    }

    public function testResetRateLimitForDifferentIp(): void
    {
        $route = 'foo';
        $ip = '1.1.1.1';
        $id = sha1($ip.$route);

        $cache = $this->getCache();
        $this->createItem($id);
        $this->assertTrue($cache->getItem($id)->isHit());

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->resetAttemptsForIpAndRoute('1.1.1.2', $route);
        $this->assertTrue($cache->getItem($id)->isHit());
    }

    public function testHandlingRequestsForDefinedRoute(): void
    {
        $route = 'foo';
        $ip = '127.0.0.1';
        $id = sha1($ip.$route);
        $request = Request::create('/foo', 'GET', ['_route' => $route]);

        $event = $this->createMock(RequestEvent::class);
        $event->method('isMasterRequest')->willReturn(true);
        $event->method('getRequest')->willReturn($request);

        $cache = $this->getCache();
        $this->assertFalse($cache->getItem($id)->isHit());

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->handleRequest($event);
        $this->assertTrue($cache->getItem($id)->isHit());

        $cacheEntry = $cache->getItem($id);
        $this->assertEquals(1, $cacheEntry->get());

        $manager->handleRequest($event);
        $this->assertTrue($cache->getItem($id)->isHit());

        $cacheEntry = $cache->getItem($id);
        $this->assertEquals(2, $cacheEntry->get());

        $manager->handleRequest($event);
        $this->assertTrue($cache->getItem($id)->isHit());

        $cacheEntry = $cache->getItem($id);
        $this->assertEquals(3, $cacheEntry->get());
    }

    public function testHandlingRequestsForNotDefinedRoute(): void
    {
        $route = 'foo2';
        $ip = '127.0.0.1';
        $id = sha1($ip.$route);
        $request = Request::create('/foo', 'GET', ['_route' => $route]);

        $event = $this->createMock(RequestEvent::class);
        $event->method('isMasterRequest')->willReturn(true);
        $event->method('getRequest')->willReturn($request);

        $cache = $this->getCache();
        $this->assertFalse($cache->getItem($id)->isHit());

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->handleRequest($event);
        $this->assertFalse($cache->getItem($id)->isHit());
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
