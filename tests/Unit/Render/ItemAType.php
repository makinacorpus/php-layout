<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\RenderCollection;
use MakinaCorpus\Layout\Type\ItemTypeInterface;

class ItemAType implements ItemTypeInterface
{
    public function getType() : string
    {
        return 'a';
    }

    public function isValid(string $id) : bool
    {
        return true;
    }

    public function create(string $id, array $options = []) : ItemInterface
    {
        return new Item('a', $id);
    }

    public function preload(array $items)
    {
    }

    public function renderItem(ItemInterface $item, RenderCollection $collection) : string
    {
        return '<item id="A' . $item->getId() . '"/>';
    }

    public function renderAllItems(array $items, RenderCollection $collection) : array
    {
        $ret = [];

        foreach ($items as $item) {
            $ret[$item->getId()] = $this->renderItem($item, $collection);
        }

        return $ret;
    }
}
