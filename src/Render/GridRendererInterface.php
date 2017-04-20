<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;

/**
 * By implementing this interface, you can plug containers rendering into
 * your own application and CSS grid.
 */
interface GridRendererInterface
{
    /**
     * Get allowed column styles
     *
     * It may return an empty array if there is no specific styles for columns.
     *
     * @return string[]
     *   Keys are internal names, values are human readable descriptions,
     *   responsability of translating them goes to the using framework or
     *   application
     */
    public function getColumnStyles() : array;

    /**
     * Render an vertical container
     *
     * @param TopLevelContainer $container
     * @param string $innerHtml
     *
     * @return string
     */
    public function renderTopLevelContainer(TopLevelContainer $container, string $innerHtml) : string;

    /**
     * Render an horizontal container single column
     *
     * @param ColumnContainer $container
     * @param string $innerHtml
     *
     * @return string
     */
    public function renderColumnContainer(ColumnContainer $container, string $innerHtml) : string;

    /**
     * Render an horizontal container
     *
     * @param HorizontalContainer $container
     * @param string[] $columnsHtml
     *
     * @return string
     */
    public function renderHorizontalContainer(HorizontalContainer $container, array $columnsHtml) : string;

    /**
     * Render an item
     *
     * @param ItemInterface $item
     * @param ContainerInterface $parent
     * @param string $innerHtml
     * @param int $position
     *
     * @return string
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, string $innerHtml, int $position) : string;
}
