<?php

namespace MakinaCorpus\Layout\Tests\Unit\Storage;

use MakinaCorpus\Layout\Grid\VerticalContainer;
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
        $this->container = new VerticalContainer('layout-' . $id);
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
    public function getTopLevelContainer() : VerticalContainer
    {
        return $this->container;
    }
}
