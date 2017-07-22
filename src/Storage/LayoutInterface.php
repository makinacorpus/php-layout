<?php

namespace MakinaCorpus\Layout\Storage;

use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;

/**
 * Grid storage interface
 */
interface LayoutInterface
{
    /**
     * Layout edit permission string
     */
    const PERMISSION_EDIT = 'edit';

    /**
     * Get layout identifier
     *
     * @return int
     */
    public function getId() : int;

    /**
     * Is layout temporary
     *
     * @return bool
     */
    public function isTemporary() : bool;

    /**
     * Get top level container
     *
     * @return TopLevelContainer
     */
    public function getTopLevelContainer() : TopLevelContainer;

    /**
     * Find the target item
     *
     * @param int $itemId
     *
     * @return ItemInterface
     */
    public function findItem(int $itemId) : ItemInterface;

    /**
     * Find the target container
     *
     * @param int $itemId
     *
     * @return ContainerInterface
     */
    public function findContainer(int $itemId) : ContainerInterface;

    /**
     * From the given layout, find the target item
     *
     * @param int $itemId
     *
     * @return ContainerInterface
     */
    public function findContainerOf(int $itemId) : ContainerInterface;
}
