<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;

/**
 * Temporary object that collects rendered items
 *
 * @todo for rendered items, style should be part of the key too
 */
final class RenderCollection
{
    /**
     * Circular dependency breaker
     */
    private $index = [];

    /**
     * Type map of items
     */
    private $itemPerTypeMap;

    /**
     * This is the sorted list of containers
     */
    private $containers = [];

    /**
     * This is a type/id map of rendered item strings
     */
    private $rendered = [];

    /**
     * Circular dependency breaker
     *
     * @param ItemInterface $item
     *
     * @return bool
     */
    public function has(ItemInterface $item) : bool
    {
        return isset($this->index[$item->getType()][$item->getId()]);
    }

    /**
     * Add items
     *
     * @param ItemInterface $item
     */
    public function addItem(ItemInterface $item)
    {
        $type = $item->getType();
        $id = $item->getId();

        $this->index[$type][$id] = true;

        if ($item instanceof ContainerInterface) {
            $this->containers[$type . '#' . $id] = $item;
        } else {
            $this->itemPerTypeMap[$type][$id] = $item;
        }
    }

    /**
     * Get all containers, in appearance/dependency reverse order
     *
     * @return ContainerInterface[]
     */
    public function getAllContainers() : array
    {
        return $this->containers;
    }

    /**
     * Get item map
     *
     * @return ItemInterface[][]
     */
    public function getItemMap() : array
    {
        return $this->itemPerTypeMap;
    }

    /**
     * Add rendered list of items
     *
     * @param string $type
     * @param string[] $itemList
     */
    public function addRenderedItemAll(string $type, array $itemList)
    {
        foreach ($itemList as $id => $output) {
            $this->rendered[$type][$id] = $output;
        }
    }

    /**
     * Add a single rendered item
     *
     * @param string $type
     * @param string $id
     * @param string[] $itemList
     */
    public function addRenderedItem(string $type, string $id, $output)
    {
        $this->rendered[$type][$id] = $output;
    }

    /**
     * Get rendered item
     *
     * @param ItemInterface $item
     * @param bool $throwExceptions
     *
     * @return string
     */
    public function getRenderedItem(ItemInterface $item, bool $throwExceptions = false) : string
    {
        $type = $item->getType();
        $id = $item->getId();

        if (!isset($this->rendered[$type][$id])) {
            if ($throwExceptions) {
                throw new GenericError(sprintf("item %s, %s has not been rendered yet", $type, $id));
            }

            // Silent fallback
            return '';
        }

        return $this->rendered[$type][$id];
    }
}
