<?php

namespace App\symfony\framework_bundle;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * !!! NINCS HASZNALVA
 */
class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        dd('sdfdsfsdfsdfsdfsdfsdfsdfsdf');
//        $configDir = __DIR__ . '/../Resources/config';
        $configDir = __DIR__ . '/../../config';
//        dd($configDir);
        $loader = new YamlFileLoader($container, new FileLocator($configDir));
        $c = $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('stulipan.traducible');
        $definition->setArgument(0, $config['unicorns_are_real']);
        $definition->setArgument(1, $config['min_sunshine']);

//        dd($definition->getArguments());
    }

//    public function getAlias()
//    {
//        return 'stulipan_traducible_doi';
//    }

}