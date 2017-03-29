<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Container representation, a container is also an item
 */
interface ContainerInterface extends \Countable
{
    /**
     * This type of item type string
     */
    const HORIZONTAL_CONTAINER = 'hbox';

    /**
     * This type of item type string
     */
    const VERTICAL_CONTAINER = 'vbox';

    /**
     * Get all items
     *
     * @return ItemInterface[]
     */
    public function getAllItems() : array;

    /**
     * Is this container empty
     *
     * @return bool
     */
    public function isEmpty() : bool;

    /**
     * {@inheritdoc}
     */
    public function count() : int;
}
