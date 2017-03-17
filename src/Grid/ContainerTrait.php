<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Container basics
 */
trait ContainerTrait
{
    private $items = [];

    /**
     * {@inheritdoc}
     */
    public function getAllItems() : array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty() : bool
    {
        return !$this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function count() : int
    {
        return count($this->items);
    }
}
