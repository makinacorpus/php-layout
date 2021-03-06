<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;

/**
 * Renders composition
 */
class Renderer
{
    private $typeRegistry;
    private $gridRenderer;

    /**
     * Default constructor
     *
     * @param ItemTypeRegistry $typeRegistry
     */
    public function __construct(ItemTypeRegistry $typeRegistry, GridRendererInterface $gridRenderer)
    {
        $this->typeRegistry = $typeRegistry;
        $this->gridRenderer = $gridRenderer;
    }

    /**
     * Collect everything
     *
     * @param ItemInterface $item
     * @param RenderCollection $map
     */
    private function collect(ItemInterface $item, RenderCollection $collection)
    {
        // Circular dependency breaker
        if ($collection->has($item)) {
            return;
        }

        // This is a bottom-top item sorting, it does first set the children
        // into the stack, so ordering will be naturally sorted, children first,
        // this ensures that leaf items will always be rendered before their
        // parents, and that in all cases, containers will always be sorted
        // last the render map will contain the rendered children.
        if ($item instanceof ContainerInterface) {
            foreach ($item->getAllItems() as $child) {
                $this->collect($child, $collection);
            }
        }

        $collection->addItem($item);
    }

    /**
     * Render container children
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    private function renderContainerChildren(ContainerInterface $container, RenderCollection $collection) : string
    {
        $output = '';

        foreach ($container->getAllItems() as $position => $item) {
            $output .= $this->gridRenderer->renderItem($item, $container, $collection->getRenderedItem($item), $position);
        }

        return $output;
    }

    /**
     * Render horizontal container columns
     *
     * @param ContainerInterface $container
     *
     * @return string[]
     */
    private function renderHorizontalContainerChildren(HorizontalContainer $container, RenderCollection $collection) : array
    {
        $ret = [];

        foreach ($container->getAllItems() as $position => $column) {
            $ret[$position] = $this->renderContainer($column, $collection);
        }

        return $ret;
    }

    /**
     * Render a single container
     *
     * @param ContainerInterface $container
     * @param RenderCollection $collection
     *
     * @return string
     */
    private function renderContainer(ContainerInterface $container, RenderCollection $collection) : string
    {
        if ($container instanceof ColumnContainer) {
            $output = $this->gridRenderer->renderColumnContainer($container, $this->renderContainerChildren($container, $collection));
        } else if ($container instanceof TopLevelContainer) {
            $output = $this->gridRenderer->renderTopLevelContainer($container, $this->renderContainerChildren($container, $collection));
        } else if ($container instanceof HorizontalContainer) {
            $output = $this->gridRenderer->renderHorizontalContainer($container, $this->renderHorizontalContainerChildren($container, $collection));
        } else {
            throw new GenericError(sprintf("%s: invalid container class", HorizontalContainer::class));
        }

        $collection->setOutputFor($container, $output);

        return $output;
    }

    /**
     * Preload and render everything in the given collection
     *
     * @param RenderCollection $collection
     */
    private function renderCollection(RenderCollection $collection)
    {
        // Render in two passes, first render all leaf items, we know for sure
        // that we don't need dependency ordering for them, so we actually can
        // process them unordered using collections.
        foreach ($collection->getItemMap() as $type => $items) {
            $handler = $this->typeRegistry->getType($type, true);
            $handler->preload($items);
            $handler->renderAllItems($items, $collection);
        }

        // But in the other end, for containers, we do need to render them
        // ordered, because we can have as many nested level of containers
        // as the user wants, but grouping them by type would bread the normal
        // bottom-top processing, so we must process them in an orderly fashion.
        foreach ($collection->getAllContainers() as $container) {
            $this->renderContainer($container, $collection);
        }
    }

    /**
     * Get grid renderer
     *
     * @return GridRendererInterface
     */
    public function getGridRenderer() : GridRendererInterface
    {
        return $this->gridRenderer;
    }

    /**
     * Renders the full composition
     *
     * Beware that if you attempt to render a single item from here, you will
     * loose the grid context, meaning the renderer cannot guess the container
     * in which the item is being rendered, and any wrapper set by the
     * MakinaCorpus\Layout\Render\GridRendererInterface::renderItem() method
     * will be lost. In this very specific use case, prefer using the
     * renderItemIn() method directly.
     *
     * @param ItemInterface $item
     *
     * @return string
     */
    public function render(ItemInterface $item) : string
    {
        $collection = new RenderCollection();

        // First collect all items and categorize them
        $this->collect($item, $collection);

        // Proceed to 2-passes collection render.
        $this->renderCollection($collection);

        return $collection->getRenderedItem($item);
    }

    /**
     * Renders a single item, along with its context
     *
     * @param ItemInterface $item
     * @param ContainerInterface $parent
     * @param int $position
     *
     * @return string
     */
    public function renderItemIn(ItemInterface $item, ContainerInterface $parent, int $position) : string
    {
        $collection = new RenderCollection();
        $collection->addItem($item);

        // Proceed to 2-passes collection render.
        $this->renderCollection($collection);

        return $this->gridRenderer->renderItem($item, $parent, $collection->getRenderedItem($item), $position);
    }

    /**
     * Render compositions for all containers
     *
     * @todo WARNING DO NOT USE IN PRODUCTION, METHOD IS NOT FIXED YET
     *
     * Algorithm is the same as the render() method except that it will
     * mutualize item preloading and item rendering in bulk.
     *
     * @param ItemInterface[] $item
     *   Keys will be the identifier used as keys of the return array
     *
     * @return string[]
     */
    public function renderAll(array $items) : array
    {
        $ret = [];

        // First collect all items and categorize them
        $collection = new RenderCollection();

        // Collect from all given containers
        foreach ($items as $item) {
            $this->collect($item, $collection);
        }

        // Proceed to 2-passes collection render.
        $this->renderCollection($collection);

        // Collect from all given containers
        foreach ($items as $index => $item) {
            $ret[$index] = $collection->getRenderedItem($item);
        }

        return $ret;
    }
}
