<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Column container, this only exists to differenciate from the vertical
 * containers and allow to refine rendering.
 *
 * We do not differenciate the type itself, it would not bring any added value
 * since the rendering pipeline is supposed to go through the grid renderer via
 * the GridRendererInterface interface.
 *
 * People might want to differenciate types, they may, but it is not officialy
 * supported by this API and it doesn't guarantee it will work gracefully
 */
class ColumnContainer extends Item implements ContainerInterface
{
    use VerticalContainerTrait;

    /**
     * @var HorizontalContainer
     */
    private $parent;

    /**
     * Default constructor
     */
    public function __construct($id = null, $style = null)
    {
        parent::__construct(ContainerInterface::VERTICAL_CONTAINER, $id ?: uniqid(), $style ?? ItemInterface::STYLE_DEFAULT);
    }

    /**
     * For internal use only
     *
     * @param HorizontalContainer $parent
     */
    public function setParent(HorizontalContainer $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent container
     *
     * @return HorizontalContainer
     */
    public function getParent() : HorizontalContainer
    {
        if (!$this->parent) {
            // @codeCoverageIgnoreStart
            throw new \BadMethodCallException("uninitialized column");
            // @codeCoverageIgnoreEnd
        }

        return $this->parent;
    }
}
