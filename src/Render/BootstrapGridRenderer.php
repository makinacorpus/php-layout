<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;

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
    private function escape(string $string) : string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
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
    private function doRenderColumn(ColumnContainer $container, array $sizes, string $innerText = '') : string
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
    public function renderTopLevelContainer(TopLevelContainer $container, string $innerHtml) : string
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
            return '<div class="' . $class . '"><div class="row"><div class="col-md-12"'. $additional . '>' . $innerHtml . '</div></div></div>';
        } else {
            return '<div'. $additional . '>' . $innerHtml . '</div>';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, string $innerHtml) : string
    {
        return $innerHtml;
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, array $columnsHtml) : string
    {
        $innerText = '';

        if (!$container->isEmpty()) {
            $innerContainers = $container->getAllItems();

            // @todo find a generic way to push column sizes into configuration
            //   and the user customize it
            $defaultSize = floor(12 / count($innerContainers));

            foreach ($innerContainers as $position => $child) {
                $innerText .= $this->doRenderColumn(
                    $child,
                    ['md' => $defaultSize],
                    $columnsHtml[$position]
                );
            }
        }

        $additional = ' data-id="' . $this->escape($container->getGridIdentifier()) . '"';

        return '<div class="row"'. $additional . '>' . $innerText . '</div>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, string $innerHtml, int $position) : string
    {
        return $innerHtml;
    }
}
