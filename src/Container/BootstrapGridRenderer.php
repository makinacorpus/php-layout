<?php

namespace MakinaCorpus\Layout\Container;

use MakinaCorpus\Layout\Grid\ArbitraryContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * Bootstrap 3 compatible grid renderer.
 */
class BootstrapGridRenderer implements GridRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function renderAbritraryContainer(ArbitraryContainer $container, RenderCollection $collection)
    {
        $output = '<container id="' . $container->getId() . '">';

        foreach ($container->getAllItems() as $child) {
            $output .= $collection->getRenderedItem($child, true);
        }

        return $output . '</container>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, RenderCollection $collection)
    {
        $output = '<horizontal id="' . $container->getId() . '">';

        foreach ($container->getAllItems() as $child) {
            $output .= $collection->getRenderedItem($child, true);
        }

        return $output . '</horizontal>';
    }
}
