<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\VerticalContainer;
use MakinaCorpus\Layout\Render\GridRendererInterface;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * Unit test only container renderer: outputs simple XML like string to allow
 * easy non-regression testing using the XML.
 */
class XmlGridRenderer implements GridRendererInterface
{
    /**
     * Render as XML element
     *
     * @param ContainerInterface $container
     * @param RenderCollection $collection
     * @param string $element
     *
     * @return string
     */
    private function renderAsElement(ContainerInterface $container, RenderCollection $collection, string $element) : string
    {
        $output = '<' . $element . ' id="' . $collection->identify($container) . '">';

        foreach ($container->getAllItems() as $child) {
            $output .= $collection->getRenderedItem($child, true);
        }

        return $output . '</' . $element . '>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderVerticalContainer(VerticalContainer $container, RenderCollection $collection) : string
    {
        return $this->renderAsElement($container, $collection, 'vertical');
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, RenderCollection $collection) : string
    {
        return $this->renderAsElement($container, $collection, 'column');
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, RenderCollection $collection) : string
    {
        return $this->renderAsElement($container, $collection, 'horizontal');
    }
}
