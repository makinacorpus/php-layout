<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\RenderCollection;
use MakinaCorpus\Layout\Type\ItemTypeInterface;

class ItemBType implements ItemTypeInterface
{
    public function getType() : string
    {
        return 'b';
    }

    public function isValid(string $id) : bool
    {
        return true;
    }

    public function create(string $id, string $style = null, array $options = []) : ItemInterface
    {
        return new Item('b', $id);
    }

    public function preload(array $items)
    {
    }

    public function renderItem(ItemInterface $item, RenderCollection $collection) : string
    {
        return '<item id="' . $collection->identify($item) . '"/>';
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
