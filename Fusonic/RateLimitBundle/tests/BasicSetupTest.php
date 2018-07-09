<?php

namespace Fusonic\RateLimitBundle\Tests;

use Doctrine\Common\Cache\CacheProvider;
use Fusonic\RateLimitBundle\EventSubscriber\RateLimitEventSubscriber;
use Fusonic\RateLimitBundle\Manager\RateLimitManager;

class BasicSetupTest extends TestCase
{
    public function testServiceWiring(): void
    {
        $cacheProvider = $this->container->get('fusonic_rate_limit.cache_provider');
        $this->assertInstanceOf(CacheProvider::class, $cacheProvider);

        $manager = $this->container->get('fusonic_rate_limit.manager');
        $this->assertInstanceOf(RateLimitManager::class, $manager);

        $subscriber = $this->container->get('fusonic_rate_limit.event_subscriber');
        $this->assertInstanceOf(RateLimitEventSubscriber::class, $subscriber);
    }
}
