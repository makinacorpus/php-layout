<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Render\GridRendererInterface;

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
     * @param string $innerHtml
     * @param string $element
     *
     * @return string
     */
    private function renderAsElement(ContainerInterface $container, string $innerHtml, string $element) : string
    {
        if ($container instanceof ItemInterface) {
            $identifier = $container->getGridIdentifier();
        } else {
            $identifier = 'container';
        }

        return '<' . $element . ' id="' . $identifier . '">' . $innerHtml . '</' . $element . '>';
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnStyles() : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, string $innerHtml, array $attributes = []) : string
    {
        return $this->renderAsElement($container, $innerHtml, 'vertical');
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, string $innerHtml, array $attributes = []) : string
    {
        return $this->renderAsElement($container, $innerHtml, 'column');
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, array $columnsHtml, array $attributes = []) : string
    {
        return $this->renderAsElement($container, implode('', $columnsHtml), 'horizontal');
    }

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, string $innerHtml, int $position, array $attributes = []) : string
    {
        return $innerHtml;
    }
}
