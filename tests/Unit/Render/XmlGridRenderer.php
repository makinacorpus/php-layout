<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Container\GridRendererInterface;
use MakinaCorpus\Layout\Grid\ArbitraryContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * Unit test only container renderer: outputs simple XML like string to allow
 * easy non-regression testing using the XML.
 */
class XmlGridRenderer implements GridRendererInterface
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
