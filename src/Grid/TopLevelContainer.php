<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Top level container is not storable, it represents a layout root.
 */
class TopLevelContainer extends Item implements ContainerInterface
{
    use VerticalContainerTrait;

    /**
     * Default constructor
     */
    public function __construct($id = null)
    {
        parent::__construct(ContainerInterface::VERTICAL_CONTAINER, $id ?: uniqid());
    }
}
