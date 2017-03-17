<?php

namespace MakinaCorpus\Layout\Type;

use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * Null type in order to avoid production wsod's
 */
final class NullType implements ItemTypeInterface
{
    private $type;

    /**
     * Default constructor
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(string $id) : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $id, array $options = []) : ItemInterface
    {
        return new Item($this->type, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function preload(array $items)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, RenderCollection $collection) : string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function renderAllItems(array $items, RenderCollection $collection) : array
    {
        return [];
    }
}
