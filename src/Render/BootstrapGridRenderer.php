<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * Bootstrap 3 compatible grid renderer.
 */
class BootstrapGridRenderer implements GridRendererInterface
{
    /**
     * Escape string
     *
     * @param string $string
     *
     * @return string
     */
    protected function escape(string $string) : string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Render column
     *
     * @param TopLevelContainer $container
     * @param string $innerText
     *
     * @return string
     */
    protected function doRenderTopLevelContainer(TopLevelContainer $container, string $innerText = '') : string
    {
        $putContainer = false;

        if (!$container->getOption('container-none')) {
            $putContainer = true;

            if ($container->getOption('container-fluid')) {
                $class = 'container-fluid';
            } else {
                $class = 'container';
            }
        }

        $additional = ' data-id="' . $this->escape($container->getGridIdentifier()) . '"';
        $additional .= ' data-contains=0';

        if ($putContainer) {
            return '<div class="' . $class . '"><div class="row"><div class="col-md-12"'. $additional . '>' . $innerText . '</div></div></div>';
        } else {
            return '<div'. $additional . $container . '>' . $innerText . '</div>';
        }
    }

    /**
     * Render column
     *
     * @param HorizontalContainer $container
     * @param string $innerText
     *
     * @return string
     */
    protected function doRenderHorizontalContainer(HorizontalContainer $container, string $innerText = '') : string
    {
        $additional = ' data-id="' . $this->escape($container->getGridIdentifier()) . '"';

        return '<div class="row"'. $additional . '>' . $innerText . '</div>';
    }

    /**
     * Render column
     *
     * @param ColumnContainer $container
     * @param string[] $sizes
     *   An array of size, keys are media display identifiers mapping to
     *   bootstrap own prefixes (xs, sm, md, lg) and values are the width
     *   on the bootstrap grid for those medias.
     * @param string $innerText
     *
     * @return string
     */
    protected function doRenderColumn(ColumnContainer $container, array $sizes, string $innerText = '') : string
    {
        $classes = [];
        foreach ($sizes as $media => $size) {
            $classes[] = 'col-' . $media . '-' . $size;
        }

        $classAttr = implode(' ', $classes);
        $additional = ' data-id="' . $this->escape($container->getGridIdentifier()) . '" data-contains=1';

        return '<div class="' . $classAttr . '"' . $additional . '>' . $innerText . '</div>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, RenderCollection $collection) : string
    {
        $innerText = '';
        foreach ($container->getAllItems() as $position => $child) {
            $innerText .= $this->renderItem($child, $container, $collection, $position);
        }

        return $this->doRenderTopLevelContainer($container, $innerText);
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, RenderCollection $collection) : string
    {
        $innerText = '';
        foreach ($container->getAllItems() as $position => $child) {
            $innerText .= $this->renderItem($child, $container, $collection, $position);
        }

        return $innerText;
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, RenderCollection $collection) : string
    {
        $innerText = '';

        if (!$container->isEmpty()) {
            $innerContainers = $container->getAllItems();

            // @todo find a generic way to push column sizes into configuration
            //   and the user customize it
            $defaultSize = floor(12 / count($innerContainers));

            foreach ($innerContainers as $child) {
                $innerText .= $this->doRenderColumn(
                    $child,
                    ['md' => $defaultSize],
                    $collection->getRenderedItem($child)
                );
            }
        }

        return $this->doRenderHorizontalContainer($container, $innerText);
    }

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, RenderCollection $collection, int $position) : string
    {
        return $collection->getRenderedItem($item, false);
    }
}
