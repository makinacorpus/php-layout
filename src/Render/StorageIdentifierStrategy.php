<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ItemInterface;

/**
 * Identifier strategy that exposes storage identifiers and fallbacks
 * on random
 */
class StorageIdentifierStrategy implements IdentifierStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function compute(ItemInterface $item) : string
    {
        $storageId = $item->getStorageId();

        if ($storageId) {
            return $storageId;
        }

        return $item->getType() . '-' . $item->getId();
    }
}
