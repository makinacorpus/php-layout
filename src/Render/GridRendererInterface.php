<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * By implementing this interface, you can plug containers rendering into
 * your own application and CSS grid.
 */
interface GridRendererInterface
{
    /**
     * Render an vertical container
     *
     * @param TopLevelContainer $container
     * @param RenderCollection $collection
     *
     * @return string
     */
    public function renderTopLevelContainer(TopLevelContainer $container, RenderCollection $collection) : string;

    /**
     * Render an horizontal container single column
     *
     * @param ColumnContainer $container
     * @param RenderCollection $collection
     *
     * @return string
     */
    public function renderColumnContainer(ColumnContainer $container, RenderCollection $collection) : string;

    /**
     * Render an horizontal container
     *
     * @param HorizontalContainer $container
     * @param RenderCollection $collection
     *
     * @return string
     */
    public function renderHorizontalContainer(HorizontalContainer $container, RenderCollection $collection) : string;

    /**
     * Render an item
     *
     * This method is supposed to be called from the other three manually by
     * the implementor, don't forget it or you'll have surprises in rendering.
     * It allows you to wrap items whichever code is necessary.
     *
     * @see MakinaCorpus\Layout\Render\BootstrapGridRenderer
     * @see MakinaCorpus\Layout\Tests\Unit\Render\XmlGridRenderer
     *   For concrete example usage of this method.
     *
     * @param ItemInterface $item
     * @param ContainerInterface $parent
     * @param RenderCollection $collection
     * @param int $position
     *
     * @return string
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, RenderCollection $collection, int $position) : string;
}
