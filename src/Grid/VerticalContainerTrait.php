<?php

namespace MakinaCorpus\Layout\Grid;

use MakinaCorpus\Layout\Error\OutOfBoundsError;
use MakinaCorpus\Layout\Error\GenericError;

/**
 * Vertical mutable item container
 */
trait VerticalContainerTrait
{
    use ContainerTrait;

    /**
     * Get item at the given position
     *
     * @param int $position
     *
     * @return ItemInterface
     */
    public function getAt(int $position) : ItemInterface
    {
        if (!isset($this->items[$position])) {
            throw new OutOfBoundsError(sprintf("%d is out of bounds, allowed: [%d-%d]", $position, 0, count($this->items)));
        }

        return $this->items[$position];
    }

    /**
     * Add item at the specified position
     *
     * @param ItemInterface $item
     * @param int $position
     *   If no position specified or int is higher to the higher bound,
     *   append the item, for prepending set 0
     */
    public function addAt(ItemInterface $item, int $position = -1)
    {
        if ($item instanceof TopLevelContainer) {
            throw new GenericError("you cannot nest a top level container");
        }
        if ($layoutId = $this->getLayoutId()) {
            $item->setLayoutId($layoutId);
        }

        if (0 === $position) {
            array_unshift($this->items, $item);
        } else if ($position < 0) {
            $this->items[] = $item;
        } else if (count($this->items) <= $position) {
            $this->items[] = $item;
        } else {
            array_splice($this->items, $position, 0, [$item]);
        }

        $this->toggleUpdateStatus(true);

        return $this;
    }

    /**
     * Remove item at specified position
     *
     * @param int $position
     */
    public function removeAt(int $position)
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
     * @param ItemInterface $item
     */
    public function prepend(ItemInterface $item)
    {
        $this->addAt($item, 0);

        return $this;
    }

    /**
     * Append item
     *
     * @param ItemInterface $item
     */
    public function append(ItemInterface $item)
    {
        $this->addAt($item, -1);

        return $this;
    }
}
