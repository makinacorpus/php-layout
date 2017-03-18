<?php

namespace MakinaCorpus\Layout\Container;

use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\VerticalContainer;
use MakinaCorpus\Layout\Render\RenderCollection;
use MakinaCorpus\Layout\Type\ItemTypeInterface;
use MakinaCorpus\Layout\Grid\ColumnContainer;

/**
 * Default vertical container type
 */
class VerticalContainerType implements ItemTypeInterface
{
    /**
     * @var GridRendererInterface
     */
    private $gridRenderer;

    /**
     * Default constructor
     *
     * @param GridRendererInterface $renderer
     */
    public function __construct(GridRendererInterface $gridRenderer)
    {
        $this->gridRenderer = $gridRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return VerticalContainer::VERTICAL_CONTAINER;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(string $id) : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $id, array $options = []) : ItemInterface
    {
        return new VerticalContainer($id);
    }

    /**
     * {@inheritdoc}
     */
    public function preload(array $items)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, RenderCollection $collection) : string
    {
        if ($item instanceof ColumnContainer) {
            return $this->gridRenderer->renderColumnContainer($item, $collection);
        }

        return $this->gridRenderer->renderVerticalContainer($item, $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function renderAllItems(array $items, RenderCollection $collection) : array
    {
        $ret = [];

        foreach ($items as $item) {
            $ret[$item->getId()] = $this->renderItem($item, $collection);
        }

        return $ret;
    }
}
