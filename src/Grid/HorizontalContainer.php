<?php

namespace MakinaCorpus\Layout\Grid;

use MakinaCorpus\Layout\Error\OutOfBoundsError;

/**
 * Horizontal split container is basis of container grids: they can only cary
 * container themselves, but nested containers are always vertical containers
 * that can contain anything.
 */
class HorizontalContainer extends Item implements ContainerInterface
{
    use ContainerTrait;

    /**
     * Default constructor
     *
     * @param int|string $id
     *   Can be null
     */
    public function __construct($id = null, $style = null)
    {
        parent::__construct(ContainerInterface::HORIZONTAL_CONTAINER, $id ?: uniqid(), $style ?? ItemInterface::STYLE_DEFAULT);
    }

    /**
     * Get row at the given position
     *
     * @param int $position
     *
     * @return ColumnContainer
     */
    public function getColumnAt(int $position) : ColumnContainer
    {
        if (!isset($this->items[$position])) {
            throw new OutOfBoundsError(sprintf("%d is out of bounds, allowed: [%d-%d]", $position, 0, count($this->items)));
        }

        return $this->items[$position];
    }

    /**
     * Add item at the specified position
     *
     * @param int $position
     *   If no position specified or int is higher to the higher bound,
     *   append the item, for prepending set 0
     * @param int|string $id
     *   Column identifier, can be null
     *
     * @return ColumnContainer
     *   The added column
     */
    public function createColumnAt(int $position = -1, $id = null) : ColumnContainer
    {
        $container = new ColumnContainer($id);
        $container->setParent($this);

        // For edition scenarios, one must have a layout identifier
        $layoutId = $this->getLayoutId();
        if ($layoutId) {
            $container->setLayoutId($layoutId);
        }

        if (0 === $position) {
            array_unshift($this->items, $container);
        } else if ($position < 0) {
            $this->items[] = $container;
        } else if (count($this->items) <= $position) {
            $this->items[] = $container;
        } else {
            array_splice($this->items, $position, 0, [$container]);
        }

        $this->toggleUpdateStatus(true);

        return $container;
    }

    /**
     * Remove item at specified position
     *
     * @param int $position
     *
     * @return $this
     */
    public function removeColumnAt(int $position) : HorizontalContainer
    {
        if (!isset($this->items[$position])) {
            throw new OutOfBoundsError(sprintf("%d is out of bounds, allowed: [%d-%d]", $position, 0, count($this->items) - 1));
        }

        if ($position < count($this->items)) {
            array_splice($this->items, $position, 1);
        }

        $this->toggleUpdateStatus(true);

        return $this;
    }

    /**
     * Prepend item
     *
     * @param int|string $id
     *   Column identifier, can be null
     *
     * @return ColumnContainer
     *   The added column
     */
    public function prependColumn($id = null) : ColumnContainer
    {
        return $this->createColumnAt(0, $id);
    }

    /**
     * Append item
     *
     * @param int|string $id
     *   Column identifier, can be null
     *
     * @return ColumnContainer
     *   The added column
     */
    public function appendColumn($id = null) : ColumnContainer
    {
        return $this->createColumnAt(-1, $id);
    }

    /**
     * Pending unserialization, some references must be restored
     */
    public function __wakeUp()
    {
        $this->restoreChildrenLayoutId();

        /** @var \MakinaCorpus\Layout\Grid\ColumnContainer $item */
        foreach ($this->items as $item) {
            $item->setParent($this);
        }
    }
}
