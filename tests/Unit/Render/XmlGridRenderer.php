<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
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
        if ($container instanceof ItemInterface) {
            $identifier = $container->getGridIdentifier();
        } else {
            $identifier = 'container';
        }

        $output = '<' . $element . ' id="' . $identifier . '">';

        foreach ($container->getAllItems() as $position => $child) {
            $output .= $this->renderItem($child, $container, $collection, $position);
        }

        return $output . '</' . $element . '>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, RenderCollection $collection) : string
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

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, RenderCollection $collection, int $position) : string
    {
        return $collection->getRenderedItem($item, true);
    }
}
