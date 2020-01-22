<?php

namespace Fusonic\RateLimitBundle\Tests\Manager;

use Fusonic\RateLimitBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RateLimitManagerTest extends TestCase
{
    public function testResetRateLimitForRoute(): void
    {
        $route = 'foo';
        $ip = '1.1.1.1';
        $id = sha1($ip.$route);

        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $cache->save($id, 5, 3600);
        $this->assertTrue($cache->contains($id));

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->resetAttemptsForIpAndRoute($ip, 'foo');
        $this->assertFalse($cache->contains($id));
    }

    public function testResetRateLimitForNotExistingRoute(): void
    {
        $route = 'foo';
        $ip = '1.1.1.1';
        $id = sha1($ip.$route);

        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $cache->save($id, 5, 3600);
        $this->assertTrue($cache->contains($id));

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->resetAttemptsForIpAndRoute($ip, 'foo2');
        $this->assertTrue($cache->contains($id));
    }

    public function testResetRateLimitForDifferentIp(): void
    {
        $route = 'foo';
        $ip = '1.1.1.1';
        $id = sha1($ip.$route);

        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $cache->save($id, 5, 3600);
        $this->assertTrue($cache->contains($id));

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->resetAttemptsForIpAndRoute('1.1.1.2', $route);
        $this->assertTrue($cache->contains($id));
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

        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $this->assertFalse($cache->contains($id));

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->handleRequest($event);
        $this->assertTrue($cache->contains($id));

        $cacheEntry = $cache->fetch($id);
        $this->assertEquals(1, $cacheEntry);

        $manager->handleRequest($event);
        $this->assertTrue($cache->contains($id));

        $cacheEntry = $cache->fetch($id);
        $this->assertEquals(2, $cacheEntry);

        $manager->handleRequest($event);
        $this->assertTrue($cache->contains($id));

        $cacheEntry = $cache->fetch($id);
        $this->assertEquals(3, $cacheEntry);
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

        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $this->assertFalse($cache->contains($id));

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $manager->handleRequest($event);
        $this->assertFalse($cache->contains($id));
    }
}
