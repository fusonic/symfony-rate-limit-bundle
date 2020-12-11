<?php

namespace Fusonic\RateLimitBundle;

use Fusonic\RateLimitBundle\DependencyInjection\CacheProviderPass;
use Fusonic\RateLimitBundle\DependencyInjection\RateLimitExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RateLimitBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new RateLimitExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CacheProviderPass());
    }
}
