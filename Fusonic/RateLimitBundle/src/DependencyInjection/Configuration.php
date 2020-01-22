<?php

namespace Fusonic\RateLimitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fusonic_rate_limit');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
                ->canBeEnabled()
                ->children()
                    ->scalarNode('cache_provider')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->children()
                    ->arrayNode('routes')
                        ->isRequired()
                        ->requiresAtLeastOneElement()
                        ->useAttributeAsKey('route_name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('limit')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('period')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
