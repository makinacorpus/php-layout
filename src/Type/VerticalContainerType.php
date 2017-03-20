<?php

namespace MakinaCorpus\Layout\Type;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\VerticalContainer;
use MakinaCorpus\Layout\Render\GridRendererInterface;
use MakinaCorpus\Layout\Render\RenderCollection;

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
    public function create(string $id, string $style = null, array $options = []) : ItemInterface
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
    public function renderItem(ItemInterface $item, RenderCollection $collection)
    {
        if ($item instanceof ColumnContainer) {
            $output = $this->gridRenderer->renderColumnContainer($item, $collection);
        } else {
            $output = $this->gridRenderer->renderVerticalContainer($item, $collection);
        }

        $collection->addRenderedItem($item, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function renderAllItems(array $items, RenderCollection $collection)
    {
        foreach ($items as $item) {
            $this->renderItem($item, $collection);
        }
    }
}
