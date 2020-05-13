<?php

namespace Fusonic\RateLimitBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CacheProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $cacheServiceId = $container->getParameter('fusonic_rate_limit')['cache_provider'];

        $container->setAlias('fusonic_rate_limit.cache_provider', $cacheServiceId);

        $definition = $container->getDefinition('fusonic_rate_limit.manager');
        $definition->replaceArgument(1, new Reference($cacheServiceId));
    }
}
