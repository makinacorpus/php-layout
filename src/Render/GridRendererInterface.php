<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
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
}
