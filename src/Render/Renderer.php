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
    private $typeRegistry;

    /**
     * Default constructor
     *
     * @param ItemTypeRegistry $typeRegistry
     */
    public function __construct(ItemTypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
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
     * Renders the full composition
     *
     * @param ContainerInterface $container
     *
     * @return string
     */
    public function render(ContainerInterface $container) : string
    {
        // First collect all items and categorize them
        $collection = new RenderCollection();
        $this->collect($container, $collection);

        // Render in two passes, first render all leaf items, we know for sure
        // that we don't need dependency ordering for them, so we actually can
        // process them unordered using collections.
        foreach ($collection->getItemMap() as $type => $items) {
            $handler = $this->typeRegistry->getType($type, true);
            $handler->preload($items);
            $collection->addRenderedItemAll($type, $handler->renderAllItems($items, $collection));
        }

        // But in the other end, for containers, we do need to render them
        // ordered, because we can have as many nested level of containers
        // as the user wants, but grouping them by type would bread the normal
        // bottom-top processing, so we must process them in an orderly fashion.
        // @todo this imply we cannot preload the containers
        foreach ($collection->getAllContainers() as $container) {
            $type = $container->getType();

            $collection->addRenderedItem(
                $type,
                $container->getId(),
                $this
                    ->typeRegistry
                    ->getType($type)
                    ->renderItem($container, $collection)
            );
        }

        // Finally, render the top level container by giving it all
        // rendered children, it should work
        return $this->typeRegistry->getType($container->getType())->renderItem($container, $collection);
    }
}
