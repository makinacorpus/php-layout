<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ItemInterface;

/**
 * Objects implementing this must be able to compute unique identifiers
 * from any items, and parse them. This will be the component responsible
 * for identifying items on the front side, which will allows to enable
 * a javascript UI over the layout composition.
 *
 * This is an interface because those identifiers are also meant to be
 * parsed from the front side, it let API users replace this implementation
 * with something better suited for their own needs.
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

    /**
     * Parse a unique identifier from item
     *
     * @param string $identifier
     *
     * @return array
     *   An array whose values are the item type and the item identifier
     *
     * @throws InvalidIdentifierError
     *   In case the identifier is invalid
     */
    public function parse(string $identifier) : array;
}
