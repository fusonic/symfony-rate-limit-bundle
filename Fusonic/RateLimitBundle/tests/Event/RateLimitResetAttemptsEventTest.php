<?php

namespace Fusonic\RateLimitBundle\Tests\Event;

use Fusonic\RateLimitBundle\Event\RateLimitResetAttemptsEvent;
use Fusonic\RateLimitBundle\Tests\TestCase;

class RateLimitResetAttemptsEventTest extends TestCase
{
    public function testGetters(): void
    {
        $ip = '1.1.1.1';
        $route = 'foo';
        $event = new RateLimitResetAttemptsEvent($route, $ip);

        $this->assertEquals($ip, $event->getIp());
        $this->assertEquals($route, $event->getRoute());
    }
}
