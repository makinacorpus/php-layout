<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Error\InvalidIdentifierError;
use MakinaCorpus\Layout\Grid\ContainerInterface;

/**
 * Default usage implementation
 *
 * @todo
 *  - escaping at some point?
 *  - enforce type and identifier naming constraints on items?
 */
class DefaultIdentifierStrategy implements IdentifierStrategyInterface
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

    /**
     * {@inheritdoc}
     */
    public function parse(string $identifier) : array
    {
        $index = strpos($identifier, ':');
        if (!$index) {
            // We don't check with false, because position 0 would mean
            // there is no type
            throw new InvalidIdentifierError(sprintf("%s: invalid identifier", $identifier));
        }

        list(, $real) = explode(':', $identifier, 2);

        $index = strpos($real, '/');
        if (!$index) {
            // We don't check with false, because position 0 would mean
            // there is no type
            throw new InvalidIdentifierError(sprintf("%s: invalid identifier", $identifier));
        }

        return explode('/', $real, 2);
    }
}
