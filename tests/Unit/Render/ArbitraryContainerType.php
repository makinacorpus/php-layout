<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\ArbitraryContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\RenderCollection;
use MakinaCorpus\Layout\Type\ItemTypeInterface;

class ArbitraryContainerType implements ItemTypeInterface
{
    public function getType() : string
    {
        return ArbitraryContainer::ARBITRARY_CONTAINER;
    }

    public function isValid(string $id) : bool
    {
        return true;
    }

    public function create(string $id, array $options = []) : ItemInterface
    {
        return new ArbitraryContainer($id);
    }

    public function preload(array $items)
    {
    }

    public function renderItem(ItemInterface $item, RenderCollection $collection) : string
    {
        /** @var \MakinaCorpus\Layout\Grid\ArbitraryContainer $item */
        $output = '<container id="' . $item->getId() . '">';

        foreach ($item->getAllItems() as $child) {
            $type = $child->getType();
            $id = $child->getId();
            $output .= $collection->getRenderedItem($type, $id, true);
        }

        return $output . '</container>';
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
