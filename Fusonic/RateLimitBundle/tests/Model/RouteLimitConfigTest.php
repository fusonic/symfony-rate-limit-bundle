<?php

namespace Fusonic\RateLimitBundle\Tests\Event;

use Fusonic\RateLimitBundle\Model\RouteLimitConfig;
use Fusonic\RateLimitBundle\Tests\TestCase;

class RouteLimitConfigTest extends TestCase
{
    public function testGetters(): void
    {
        $period = 4800;
        $route = 'foo';
        $limit = 3;
        $config = RouteLimitConfig::fromRouteConfig($route, ['limit' => $limit, 'period' => $period]);

        $this->assertEquals($route, $config->getRoute());
        $this->assertEquals($period, $config->getPeriod());
        $this->assertEquals($limit, $config->getLimit());
    }
}
