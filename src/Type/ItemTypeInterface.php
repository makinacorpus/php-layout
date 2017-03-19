<?php

namespace MakinaCorpus\Layout\Type;

use MakinaCorpus\Layout\Error\TypeMismatchError;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * Implement this to support widgets
 */
interface ItemTypeInterface
{
    /**
     * Get item type this instance supports
     *
     * @return int
     */
    public function getType() : string;

    /**
     * Is the given item valid, does it exists
     *
     * @param int|string $id
     *
     * @return bool
     */
    public function isValid(string $id) : bool;

    /**
     * Create new instance
     *
     * @param int|string $id
     * @param null|string $style
     * @param string[] $options
     *
     * @return ItemInterface
     */
    public function create(string $id, string $style = null, array $options = []) : ItemInterface;

    /**
     * Preload items data if necessary, this will be call at runtime prior
     * to full grid rendering, it allows the implementor to have access to
     * a flatten tree and preload everything it can
     *
     * @param ItemInterface[] $items
     *
     * @throws TypeMismatchError
     *   In case one of the items has not the right type
     */
    public function preload(array $items);

    /**
     * Render a single item
     *
     * @param ItemInterface $item
     *   Item to render
     * @param RenderCollection $collection
     *   Already rendered items
     *
     * @return string
     *   If the item is not renderable or invalid, return an empty string
     *
     * @throws TypeMismatchError
     *   In case the item has not the right type
     */
    public function renderItem(ItemInterface $item, RenderCollection $collection) : string;

    /**
     * Render an array of items
     *
     * @param ItemInterface[] $items
     *   Items to render
     * @param RenderCollection $collection
     *   Already rendered items
     *
     * @return string[]
     *   Array of render strings, keyed using item identifiers, invalid items
     *   must be silently removed from the returned array, no errors
     *
     * @throws TypeMismatchError
     *   In case one of the items has not the right type
     */
    public function renderAllItems(array $items, RenderCollection $collection) : array;
}
