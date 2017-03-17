<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Container representation, a container is also an item
 */
interface ContainerInterface extends ItemInterface, \Countable
{
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
