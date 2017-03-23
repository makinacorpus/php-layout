<?php

namespace MakinaCorpus\Layout\Tests\Unit\Render;

use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\IdentifierStrategyInterface;

/**
 * Do not return any identifier, for unit test for which identification
 * does not matter.
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
}
