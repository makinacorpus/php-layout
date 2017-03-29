<?php

namespace MakinaCorpus\Layout\Tests\Unit\Storage;

use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Storage\AbstractLayout;

/**
 * Mock object for stored layouts
 */
class TestLayout extends AbstractLayout
{
    private $id;
    private $container;

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->container = new TopLevelContainer('layout-' . $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getTopLevelContainer() : TopLevelContainer
    {
        return $this->container;
    }

    /**
     * Clone implementation, allows unit testing storage using object copies
     */
    public function __clone()
    {
        $this->container = clone $this->container;
    }
}
