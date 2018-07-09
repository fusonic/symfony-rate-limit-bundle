<?php

namespace Fusonic\RateLimitBundle;

use Fusonic\RateLimitBundle\DependencyInjection\CacheProviderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Fusonic\RateLimitBundle\DependencyInjection\RateLimitExtension;

class RateLimitBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new RateLimitExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheProviderPass());
    }
}
