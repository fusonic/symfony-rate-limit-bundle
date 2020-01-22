<?php

namespace Fusonic\RateLimitBundle\Tests\Event;

use Fusonic\RateLimitBundle\Event\RateLimitExceededEvent;
use Fusonic\RateLimitBundle\Model\RouteLimitConfig;
use Fusonic\RateLimitBundle\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RateLimitExceededEventTest extends TestCase
{
    public function testGetters(): void
    {
        $period = 4800;
        $route = 'foo';
        $limit = 3;
        $ip = '1.1.1.1';
        $config = RouteLimitConfig::fromRouteConfig($route, ['limit' => $limit, 'period' => $period]);
        $responseEvent = $event = $this->createMock(RequestEvent::class);

        $request = Request::create($route, 'GET', ['_route' => $route]);
        $event->method('getRequest')->willReturn($request);

        $event = new RateLimitExceededEvent($config, $ip, $responseEvent);
        $event->setResponse(new Response());

        $this->assertEquals($ip, $event->getIp());
        $this->assertEquals($config, $event->getRouteLimitConfig());
        $this->assertEquals($request, $event->getRequest());
    }
}
