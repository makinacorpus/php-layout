<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\IdentifierStrategyInterface;

/**
 * Default usage implementation
 *
 * @todo
 *  - escaping at some point?
 *  - enforce type and identifier naming constraints on items?
 */
class NoIdIdentifierStrategy implements IdentifierStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function compute(ItemInterface $item) : string
    {
        if ($item instanceof ContainerInterface) {
            return 'container:' . $item->getType();
        }

        return 'leaf:' . $item->getType() . '/' . $item->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $identifier) : array
    {
        throw new \LogicException("Not implemented");
    }
}
