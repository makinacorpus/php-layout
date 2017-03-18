<?php

namespace MakinaCorpus\Layout\Container;

use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\RenderCollection;
use MakinaCorpus\Layout\Type\ItemTypeInterface;

/**
 * Default horizontal container type
 */
class HorizontalContainerType implements ItemTypeInterface
{
    /**
     * @var GridRendererInterface
     */
    private $gridRenderer;

    /**
     * Default constructor
     *
     * @param GridRendererInterface $gridRenderer
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
        return HorizontalContainer::HORIZONTAL_CONTAINER;
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
        return new HorizontalContainer($id);
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
        return $this->gridRenderer->renderHorizontalContainer($item, $collection);
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
