<?php

namespace MakinaCorpus\Layout;

use MakinaCorpus\Layout\DependencyInjection\LayoutExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Symfony based application bundle implementation.
 */
class LayoutBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        // I seriously do not believe in autodiscovery, and I wanted the class
        // names to be consistent. This is explicit: make peace with it.
        return new LayoutExtension();
    }
}
