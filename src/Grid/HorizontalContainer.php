<?php

namespace MakinaCorpus\Layout\Grid;

use MakinaCorpus\Layout\Error\OutOfBoundsError;

/**
 * Horizontal split container is basis of container grids: they can only cary
 * container themselves, but nested containers are always arbitrary containers
 * that can contain anything.
 */
class HorizontalContainer extends Item implements ContainerInterface
{
    use ContainerTrait;

    /**
     * This type of item type string
     */
    const HORIZONTAL_CONTAINER = 'hbox';

    /**
     * Default constructor
     *
     * @param int|string $id
     *   Can be null
     */
    public function __construct($id = null)
    {
        parent::__construct(self::HORIZONTAL_CONTAINER, $id ?: uniqid());
    }

    /**
     * Get row at the given position
     *
     * @param int $position
     *
     * @return ArbitraryContainer
     */
    public function getColumnAt(int $position) : ArbitraryContainer
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
     * @return ArbitraryContainer
     *   The added column
     */
    public function createColumnAt(int $position = -1, $id = null) : ArbitraryContainer
    {
        $container = new ArbitraryContainer($id);

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
     * @return ArbitraryContainer
     *   The added column
     */
    public function prependColumn($id = null) : ArbitraryContainer
    {
        return $this->createColumnAt(0, $id);
    }

    /**
     * Append item
     *
     * @param int|string $id
     *   Column identifier, can be null
     *
     * @return ArbitraryContainer
     *   The added column
     */
    public function appendColumn($id = null) : ArbitraryContainer
    {
        return $this->createColumnAt(-1, $id);
    }
}
