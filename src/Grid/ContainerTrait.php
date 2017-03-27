<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Container basics
 */
trait ContainerTrait
{
    protected $items = [];

    /**
     * {@inheritdoc}
     *
     * @return ItemInterface[]
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

    /**
     * Internal function for __wakeUp() procedure.
     */
    protected function restoreChildrenLayoutId()
    {
        if ($layoutId = $this->getLayoutId()) {
            /** @var \MakinaCorpus\Layout\Grid\ColumnContainer $item */
            foreach ($this->items as $item) {
                $item->setLayoutId($layoutId);
            }
        }
    }

    /**
     * Pending unserialization, some references must be restored
     */
    public function __wakeUp()
    {
        $this->restoreChildrenLayoutId();
    }
}
