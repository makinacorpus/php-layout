<?php

namespace MakinaCorpus\Layout\Container;

use MakinaCorpus\Layout\Grid\ArbitraryContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Render\RenderCollection;
use MakinaCorpus\Layout\Type\ItemTypeInterface;

/**
 * Default arbitrary container type
 */
class ArbitraryContainerType implements ItemTypeInterface
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
        return ArbitraryContainer::ARBITRARY_CONTAINER;
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
        return new ArbitraryContainer($id);
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
        return $this->gridRenderer->renderAbritraryContainer($item, $collection);
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
