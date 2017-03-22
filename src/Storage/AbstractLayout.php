<?php

namespace MakinaCorpus\Layout\Storage;

use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;

/**
 * Base implementation for abstract layout
 */
abstract class AbstractLayout implements LayoutInterface
{
    /**
     * Recursion for findItemIn()
     *
     * @param ContainerInterface $container
     * @param int $itemId
     *
     * @return null|ItemInterface
     */
    private function recursiveFindItemIn(ContainerInterface $container, int $itemId)
    {
        /** @var \MakinaCorpus\Layout\Grid\ItemInterface $item */
        foreach ($container->getAllItems() as $item) {
            if ($itemId == $item->getStorageId()) {
                return $item;
            }
            if ($item instanceof ContainerInterface) {
                $item = $this->recursiveFindItemIn($item, $itemId);
                if ($item) {
                    return $item;
                }
            }
        }
    }

    /**
     * Find the target item
     *
     * @param int $itemId
     *
     * @return ItemInterface
     */
    final public function findItem(int $itemId) : ItemInterface
    {
        $item = $this->recursiveFindItemIn($this->getTopLevelContainer(), $itemId);

        if (!$item) {
            throw new GenericError("item %d: does not exists", $itemId);
        }

        return $item;
    }

    /**
     * Find the target container
     *
     * @param int $itemId
     *
     * @return ContainerInterface
     */
    final public function findContainer(int $itemId) : ContainerInterface
    {
        $item = $this->recursiveFindItemIn($this->getTopLevelContainer(), $itemId);

        if (!$item) {
            throw new GenericError("item %d: does not exists", $itemId);
        }
        if (!$item instanceof ContainerInterface) {
            throw new GenericError("item %d: is not a container", $itemId);
        }

        return $item;
    }

    /**
     * Recursion for findContainerOf()
     *
     * @param ContainerInterface $container
     * @param int $itemId
     *
     * @return null|ContainerInterface
     */
    private function recursiveFindContainerOf(ContainerInterface $container, int $itemId)
    {
        /** @var \MakinaCorpus\Layout\Grid\ItemInterface $item */
        foreach ($container->getAllItems() as $item) {
            if ($itemId == $item->getStorageId()) {
                return $container;
            }
            if ($item instanceof ContainerInterface) {
                $item = $this->recursiveFindContainerOf($item, $itemId);
                if ($item) {
                    return $item;
                }
            }
        }
    }

    /**
     * From the given layout, find the target item
     *
     * @param int $itemId
     *
     * @return ContainerInterface
     */
    final public function findContainerOf(int $itemId) : ContainerInterface
    {
        $item = $this->recursiveFindContainerOf($this->getTopLevelContainer(), $itemId);

        if (!$item) {
            throw new GenericError("item %d: does not exists", $itemId);
        }

        return $item;
    }
}
