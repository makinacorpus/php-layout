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
     *
     * @return ItemInterface
     */
    public function create(string $id, string $style = null) : ItemInterface;

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
     * Get allowed style list for the given item
     *
     * Please note that you may include the default key, case in which you
     * can set the label you wish to go along.
     *
     * @param ItemInterface $item
     *
     * @return string[]
     *   Keys are style identifiers, values are style names
     *
     * @throws TypeMismatchError
     *   In case one of the items has not the right type
     */
    public function getAllowedStylesFor(ItemInterface $item) : array;

    /**
     * Render a single item
     *
     * Item must be set using RenderCollection::setOutputFor()
     * or RenderCollection::setOutputWith()
     *
     * @param ItemInterface $item
     *   Item to render
     * @param RenderCollection $collection
     *   Already rendered items
     *
     * @throws TypeMismatchError
     *   In case the item has not the right type
     */
    public function renderItem(ItemInterface $item, RenderCollection $collection);

    /**
     * Render an array of items
     *
     * Each item must be set using RenderCollection::setOutputFor()
     * or RenderCollection::setOutputWith()
     *
     * @param ItemInterface[] $items
     *   Items to render
     * @param RenderCollection $collection
     *   Already rendered items
     *
     * @throws TypeMismatchError
     *   In case one of the items has not the right type
     */
    public function renderAllItems(array $items, RenderCollection $collection);
}
