<?php

namespace Fusonic\RateLimitBundle\Tests\EventSubscriber;

use Fusonic\RateLimitBundle\Event\RateLimitResetAttemptsEvent;
use Fusonic\RateLimitBundle\Tests\TestCase;
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

        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $cache->save($id, 5, 3600);
        $this->assertTrue($cache->contains($id));
        $this->dispatchResetAttemptsEvent($route, $ip);

        $this->assertFalse($cache->contains($id));
    }

    public function testRateLimitAttemptsUpdateEventHandling(): void
    {
        $route = 'foo';
        $ip = '127.0.0.1';
        $id = sha1($ip.$route);

        $request = Request::create('/foo', 'GET', ['_route' => $route]);
        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $this->assertFalse($cache->contains($id));

        $this->dispatchRequestEvent($request);

        $this->assertTrue($cache->contains($id));
        $cacheEntry = $cache->fetch($id);
        $this->assertEquals(1, $cacheEntry);

        $this->dispatchRequestEvent($request);

        $this->assertTrue($cache->contains($id));
        $cacheEntry = $cache->fetch($id);
        $this->assertEquals(2, $cacheEntry);

        $this->dispatchRequestEvent($request);

        $this->assertTrue($cache->contains($id));
        $cacheEntry = $cache->fetch($id);
        $this->assertEquals(3, $cacheEntry);
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
}
