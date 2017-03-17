<?php

namespace MakinaCorpus\Layout\Type;

use MakinaCorpus\Layout\Error\TypeDoesNotExistError;

/**
 * Implement this to support widgets
 */
final class ItemTypeRegistry
{
    private $types = [];

    /**
     * Register type
     *
     * @param ItemTypeInterface $type
     */
    public function registerType(ItemTypeInterface $type)
    {
        $this->types[$type->getType()] = $type;
    }

    /**
     * Get type
     *
     * @param string $type
     *   The item type
     * @param bool $fallbackOnNullInstance
     *   If type does not exists, should it fallback on a null instance
     *
     * @return ItemTypeInterface
     */
    public function getType(string $type, bool $fallbackOnNullInstance = true) : ItemTypeInterface
    {
        if (!isset($this->types[$type])) {
            if ($fallbackOnNullInstance) {
                $this->types[$type] = new NullType($type);
            } else {
                throw new TypeDoesNotExistError(sprintf("%s: type does not exist", $type));
            }
        }

        return $this->types[$type];
    }
}
