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

    public function create(string $id, string $style = null) : ItemInterface
    {
        return new Item('b', $id, $style ?: ItemInterface::STYLE_DEFAULT);
    }

    public function preload(array $items)
    {
    }

    public function getAllowedStylesFor(ItemInterface $item) : array
    {
        return [];
    }

    public function renderItem(ItemInterface $item, RenderCollection $collection)
    {
        $style = $item->getStyle();
        $styleAttr = '';

        if (ItemInterface::STYLE_DEFAULT !== $style) {
            $styleAttr = ' style="' . $style . '"';
        }

        $collection->setOutputWith($item->getType(), $item->getId(), $item->getStyle(), '<item id="' . $item->getGridIdentifier() . '"' . $styleAttr . '/>');
    }

    public function renderAllItems(array $items, RenderCollection $collection)
    {
        foreach ($items as $item) {
            $this->renderItem($item, $collection);
        }
    }
}
