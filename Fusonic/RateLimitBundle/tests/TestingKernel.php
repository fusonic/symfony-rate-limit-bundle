<?php

namespace Fusonic\RateLimitBundle\Tests;

use Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle;
use Fusonic\RateLimitBundle\RateLimitBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestingKernel extends Kernel
{
    /**
     * @var array
     */
    private $config;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineCacheBundle(),
            new RateLimitBundle(),
            new MonologBundle(),
        ];
    }

    public function __construct(array $config = [])
    {
        $this->config = $config;
        parent::__construct('test', true);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(
            function (ContainerBuilder $container) {
                $container->loadFromExtension('framework', ['secret' => 'foo', 'test' => true]);
                $container->loadFromExtension(
                    'doctrine_cache',
                    [
                        'providers' => [
                            'rate_limit_cache' => [
                                'file_system' => [
                                    'extension' => '.cache',
                                    'directory' => '%kernel.cache_dir%/ratelimit',
                                ],
                            ],
                        ],
                    ]
                );
                $container->loadFromExtension(
                    'monolog',
                    [
                        'handlers' => [
                            'file_log' => [
                                'type' => 'stream',
                                'path' => '%kernel.logs_dir%/%kernel.environment%.log',
                                'level' => 'debug',
                            ],
                        ],
                    ]
                );
                $container->loadFromExtension('fusonic_rate_limit', $this->config);
            }
        );
    }
}
