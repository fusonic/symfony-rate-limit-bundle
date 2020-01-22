<?php

namespace Fusonic\RateLimitBundle\Tests;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const CONFIG = [
        'enabled' => true,
        'cache_provider' => 'doctrine_cache.providers.rate_limit_cache',
        'routes' => [
            'foo' => [
                'limit' => 2,
                'period' => 3600,
            ],
        ],
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var KernelInterface
     */
    protected $kernel;

    public function __construct(array $config = null)
    {
        parent::__construct();
        $config = $config ?? self::CONFIG;
        $this->kernel = new TestingKernel($config);
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer()->get('test.service_container');
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CacheProvider $cache */
        $cache = $this->container->get('fusonic_rate_limit.cache_provider');
        $cache->deleteAll();
    }
}
