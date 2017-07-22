<?php

namespace MakinaCorpus\Layout\EventDispatcher;

use MakinaCorpus\Layout\Context\Context;
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

    private $context;
    private $layoutIdList = [];

    /**
     * Default constructor
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get context
     */
    public function getContext() : Context
    {
        return $this->context;
    }

    /**
     * Add layout to the current page
     */
    public function addLayout(int $id)
    {
        $this->context->addLayout($id);
    }

    /**
     * Add more than one layouts to the current page
     */
    public function addLayoutList(array $idList)
    {
        $this->context->addLayoutList($idList);
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
