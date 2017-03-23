<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;

/**
 * Identifier strategy that apposes item type/id couple a identifiers
 *
 * @todo
 *  - escaping at some point?
 *  - enforce type and identifier naming constraints on items?
 */
class ItemIdentifierStrategy implements IdentifierStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function compute(ItemInterface $item) : string
    {
        if ($item instanceof ContainerInterface) {
            return 'container:' . $item->getType() . '/' . $item->getId();
        }

        return 'leaf:' . $item->getType() . '/' . $item->getId();
    }
}
