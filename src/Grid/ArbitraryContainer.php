<?php

namespace MakinaCorpus\Layout\Grid;

use MakinaCorpus\Layout\Error\OutOfBoundsError;

/**
 * Arbitrary mutable item container
 */
class ArbitraryContainer extends Item implements ContainerInterface
{
    use ContainerTrait;

    /**
     * This type of item type string
     */
    const ARBITRARY_CONTAINER = 'abox';

    /**
     * Default constructor
     */
    public function __construct($id = null)
    {
        parent::__construct(self::ARBITRARY_CONTAINER, $id ?: uniqid());
    }

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
     * @param Item $item
     * @param int $position
     *   If no position specified or int is higher to the higher bound,
     *   append the item, for prepending set 0
     *
     * @return $this
     */
    public function addAt(Item $item, int $position = -1) : ArbitraryContainer
    {
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
     *
     * @return $this
     */
    public function removeAt(int $position) : ArbitraryContainer
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
     * @param Item $item
     *
     * @return $this
     */
    public function prepend(Item $item) : ArbitraryContainer
    {
        $this->addAt($item, 0);

        return $this;
    }

    /**
     * Append item
     *
     * @param Item $item
     *
     * @return $this
     */
    public function append(Item $item) : ArbitraryContainer
    {
        $this->addAt($item, -1);

        return $this;
    }
}
