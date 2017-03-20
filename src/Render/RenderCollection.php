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
     * @var IdentifierStrategyInterface
     */
    private $identifierStrategy;

    /**
     * Circular dependency breaker
     */
    private $index = [];

    /**
     * Type map of items
     */
    private $itemMap = [];

    /**
     * This is the sorted list of containers
     */
    private $containers = [];

    /**
     * This is a type/id map of rendered item strings
     */
    private $rendered = [];

    /**
     * Default constructor
     *
     * @param IdentifierStrategyInterface $identifierStrategy
     */
    public function __construct(IdentifierStrategyInterface $identifierStrategy)
    {
        $this->identifierStrategy = $identifierStrategy;
    }

    /**
     * Circular dependency breaker
     *
     * @param ItemInterface $item
     *
     * @return bool
     */
    public function has(ItemInterface $item) : bool
    {
        return isset($this->index[$item->getType()][$item->getId()][$item->getStyle()]);
    }

    /**
     * Identify item using the identifier strategy
     *
     * @param ItemInterface $item
     *
     * @return string
     */
    public function identify(ItemInterface $item) : string
    {
        return $this->identifierStrategy->compute($item);
    }

    /**
     * Add items
     *
     * @param ItemInterface $item
     */
    public function addItem(ItemInterface $item)
    {
        $type   = $item->getType();
        $id     = $item->getId();
        $style  = $item->getStyle();

        $this->index[$type][$id][$style] = true;

        if ($item instanceof ContainerInterface) {
            $this->containers[$type . '#' . $id] = $item;
        } else {
            $this->itemMap[$type][] = $item;
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
        return $this->itemMap;
    }

    /**
     * Add rendered list of items
     *
     * @param ItemInterface $item
     * @param string $otuput
     */
    public function addRenderedItem(ItemInterface $item, string $output)
    {
        $this->rendered[$item->getType()][$item->getId()][$item->getStyle()] = $output;
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
        $type   = $item->getType();
        $id     = $item->getId();
        $style  = $item->getStyle();

        if (!isset($this->rendered[$type][$id][$style])) {
            if ($throwExceptions) {
                throw new GenericError(sprintf("item %s, %s with style %s has not been rendered yet", $type, $id, $style));
            }

            // Silent fallback
            return '';
        }

        return $this->rendered[$type][$id][$style];
    }
}
