<?php

namespace MakinaCorpus\Layout\EventDispatcher;

use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Storage\LayoutStorageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event raised in order to collect page layouts
 */
class CollectLayoutEvent extends Event
{
    /**
     * Event name
     */
    const EVENT_NAME = 'php_layout:collect';

    private $layoutIdList = [];
    private $editableIndex = [];

    /**
     * Default constructor
     *
     * @param LayoutStorageInterface $storage
     */
    public function __construct(LayoutStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Add layout to the current page
     *
     * @param LayoutInterface $layout
     */
    public function addLayout($id)
    {
        $this->layouts[$id] = $id;
    }

    /**
     * Add more than one layouts to the current page
     *
     * @param array $idList
     */
    public function addLayoutList(array $idList)
    {

    }

    /**
     * Get all layout identifiers list
     *
     * @return int[]
     */
    public function getLayoutIdList() : array
    {
        return $this->layoutIdList;
    }
}
