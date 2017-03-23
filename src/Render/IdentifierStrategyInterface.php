<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ItemInterface;

/**
 * Objects implementing this must be able to compute unique identifiers
 * from any items, and parse them. This will be the component responsible
 * for identifying items on the front side, which will allows to enable
 * a javascript UI over the layout composition.
 */
interface IdentifierStrategyInterface
{
    /**
     * Compute a unique identifier from item
     *
     * @param ItemInterface $item
     *
     * @return string
     */
    public function compute(ItemInterface $item) : string;
}
