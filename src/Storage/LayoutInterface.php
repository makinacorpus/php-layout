<?php

namespace MakinaCorpus\Layout\Storage;

use MakinaCorpus\Layout\Grid\VerticalContainer;

/**
 * Grid storage interface
 */
interface LayoutInterface
{
    /**
     * Get layout identifier
     *
     * @return int
     */
    public function getId() : int;

    /**
     * Get top level container
     *
     * @return VerticalContainer
     */
    public function getTopLevelContainer() : VerticalContainer;
}
