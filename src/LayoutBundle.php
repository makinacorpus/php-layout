<?php

namespace MakinaCorpus\Layout;

use MakinaCorpus\Layout\DependencyInjection\LayoutExtension;
use MakinaCorpus\Layout\DependencyInjection\Compiler\ItemTypeRegisterPass;
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
        $container->addCompilerPass(new ItemTypeRegisterPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new LayoutExtension();
    }
}
