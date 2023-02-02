<?php

namespace Stulipan\Traducible\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('stulipan_traducible');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->booleanNode('unicorns_are_real')->defaultTrue()->info('Whether or not you believe in unicorns')->end()
            ->integerNode('min_sunshine')->defaultValue(3)->info('How much do you like sunshine?')->end()
        ->end();

        return $treeBuilder;
    }
}