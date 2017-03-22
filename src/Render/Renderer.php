<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;

/**
 * Renders composition
 */
class Renderer
{
    private $identifierStrategy;
    private $typeRegistry;

    /**
     * Default constructor
     *
     * @param ItemTypeRegistry $typeRegistry
     * @param IdentifierStrategyInterface $identifierStrategy
     */
    public function __construct(ItemTypeRegistry $typeRegistry, IdentifierStrategyInterface $identifierStrategy)
    {
        $this->typeRegistry = $typeRegistry;
        $this->identifierStrategy = $identifierStrategy;
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
        // @todo this imply we cannot preload the containers
        foreach ($collection->getAllContainers() as $container) {
            $type = $container->getType();
            $this->typeRegistry->getType($type)->renderItem($container, $collection);
        }
    }

    /**
     * Renders the full composition
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    public function render(ItemInterface $item) : string
    {
        $collection = new RenderCollection($this->identifierStrategy);
        $itemType   = $this->typeRegistry->getType($item->getType());

        if ($item instanceof ContainerInterface) {

            // First collect all items and categorize them
            $this->collect($item, $collection);

            // Proceed to 2-passes collection render.
            $this->renderCollection($collection);
        }

        // Finally, render the top level container by giving it all
        // rendered children, it should work
        $itemType->renderItem($item, $collection);

        return $collection->getRenderedItem($item);
    }

    /**
     * Render compositions for all containers
     *
     * Algorithm is the same as the render() method except that it will
     * mutualize item preloading and item rendering in bulk.
     *
     * @param ContainerInterface[] $containers
     *   Keys will be the identifier used as keys of the return array
     *
     * @return string[]
     */
    public function renderAll(array $containers) : array
    {
        $ret = [];

        // First collect all items and categorize them
        $collection = new RenderCollection($this->identifierStrategy);

        // Collect from all given containers
        // @todo this causes problems because containers with same
        //   identifiers do conflict between layouts and output gets
        //   broken
        foreach ($containers as $container) {
            $this->collect($container, $collection);
        }

        // Proceed to 2-passes collection render.
        $this->renderCollection($collection);

        // Collect from all given containers
        foreach ($containers as $index => $container) {
            $this->typeRegistry->getType($container->getType())->renderItem($container, $collection);

            $ret[$index] = $collection->getRenderedItem($container);
        }

        return $ret;
    }
}
