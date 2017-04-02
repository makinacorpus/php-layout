<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Render\RenderCollection;

/**
 * Bootstrap 3 compatible grid renderer.
 */
class BootstrapGridRenderer implements GridRendererInterface
{
    /**
     * Render column
     *
     * @param string $innerText
     *
     * @return string
     */
    private function renderRow(string $innerText, string $identifier = null) : string
    {
        if ($identifier) {
            // @todo this should be escaped
            $additional = ' data-id="' . $identifier . '"';
            $container  = ' data-contains';
        } else {
            $additional = '';
            $container  = '';
        }

        return <<<EOT
<div class="row"{$additional}{$container}>
  {$innerText}
</div>
EOT;
    }

    /**
     * Render column
     *
     * @param string[] $sizes
     *   An array of size, keys are media display identifiers mapping to
     *   bootstrap own prefixes (xs, sm, md, lg) and values are the width
     *   on the bootstrap grid for those medias.
     * @param string $innerText
     * @param string $identifier
     *
     * @return string
     */
    private function renderColumn(array $sizes, string $innerText, string $identifier = null) : string
    {
        $classes = [];
        foreach ($sizes as $media => $size) {
            $classes[] = 'col-' . $media . '-' . $size;
        }

        $classAttr = implode(' ', $classes);

        if ($identifier) {
            // @todo this should be escaped
            $additional = ' data-id="' . $identifier . '" data-contains';
        } else {
            $additional = '';
        }

        return <<<EOT
<div class="{$classAttr}"{$additional}>
  {$innerText}
</div>
EOT;
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, RenderCollection $collection) : string
    {
        $innerText = '';
        foreach ($container->getAllItems() as $child) {
            $innerText .= $collection->getRenderedItem($child);
        }

        return '<div class="container-fluid">' . $this->renderRow($this->renderColumn(['md' => 12], $innerText, $collection->identify($container))) . '</div>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, RenderCollection $collection) : string
    {
        $innerText = '';
        foreach ($container->getAllItems() as $child) {
            $innerText .= $collection->getRenderedItem($child);
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
                $innerText .= $this->renderColumn(['md' => $defaultSize], $collection->getRenderedItem($child), $collection->identify($child));
            }
        }

        return $this->renderRow($innerText, $collection->identify($container));
    }
}
