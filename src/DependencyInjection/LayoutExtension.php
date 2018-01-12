<?php

namespace MakinaCorpus\Layout\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Usable extension for both Symfony, Drupal and may be other dependency
 * injection based environments.
 */
class LayoutExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yml');

        if (isset($container->getParameter('kernel.bundles')['GoatBundle'])) {
            $loader->load('goat.yml');
        }
    }
}
