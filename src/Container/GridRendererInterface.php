<?php

namespace MakinaCorpus\Layout\Container;

use MakinaCorpus\Layout\Grid\ArbitraryContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * By implementing this interface, you can plug containers rendering into
 * your own application and CSS grid.
 */
interface GridRendererInterface
{
    /**
     * Render an arbitrary container
     *
     * @param ArbitraryContainer $container
     * @param RenderCollection $collection
     *
     * @return string
     */
    public function renderAbritraryContainer(ArbitraryContainer $container, RenderCollection $collection);

    /**
     * Render an horizontal container
     *
     * @param HorizontalContainer $container
     * @param RenderCollection $collection
     *
     * @return string
     */
    public function renderHorizontalContainer(HorizontalContainer $container, RenderCollection $collection);
}
