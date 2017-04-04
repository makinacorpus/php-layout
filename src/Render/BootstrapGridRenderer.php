<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\Options;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Render\RenderCollection;
use MakinaCorpus\Layout\Grid\NullOptions;

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
     * @param string $innerText
     * @param Options $options
     *
     * @return string
     */
    protected function renderContainer(string $innerText, Options $options = null) : string
    {
        $options = $options ?? new NullOptions();

        if ($options->getOption('drop')) {
            return $innerText;
        }

        if ($options->getOption('fluid')) {
              $class = 'container-fluid';
        } else {
            $class = 'container';
        }

        return <<<EOT
<div class="{$class}">
  {$innerText}
</div>
EOT;
    }

    /**
     * Render column
     *
     * @param string $innerText
     * @param Options $options
     *
     * @return string
     */
    protected function renderRow(string $innerText, string $identifier = null, Options $options = null) : string
    {
        $options = $options ?? new NullOptions();

        $additional = '';
        $container = '';

        if ($identifier) {
            $additional .= ' data-id="' . $this->escape($identifier) . '"';
            $container  .= ' data-contains';
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
     * @param Options $options
     *
     * @return string
     */
    protected function renderColumn(array $sizes, string $innerText, string $identifier = null, Options $options = null) : string
    {
        $options = $options ?? new NullOptions();

        $classes = [];
        foreach ($sizes as $media => $size) {
            $classes[] = 'col-' . $media . '-' . $size;
        }

        $classAttr = implode(' ', $classes);
        $additional = '';

        if ($identifier) {
            $additional .= ' data-id="' . $this->escape($identifier) . '" data-contains';
        }

        return <<<EOT
<div class="{$classAttr}"{$additional}>
  {$innerText}
</div>
EOT;
    }

    /**
     * Render a single child
     *
     * @param ItemInterface $item
     * @param RenderCollection $collection
     *
     * @return string
     */
    protected function renderChild(ItemInterface $item, RenderCollection $collection) : string
    {
        return $collection->getRenderedItem($item, false);
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, RenderCollection $collection) : string
    {
        $innerText = '';
        foreach ($container->getAllItems() as $child) {
            $innerText .= $this->renderChild($child, $collection);
        }

        return $this->renderContainer(
            $this->renderRow(
                $this->renderColumn(['md' => 12],
                    $innerText,
                    $collection->identify($container)
                )
            ),
            $container
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, RenderCollection $collection) : string
    {
        $innerText = '';
        foreach ($container->getAllItems() as $child) {
            $innerText .= $this->renderChild($child, $collection);
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
                $innerText .= $this->renderColumn(
                    ['md' => $defaultSize],
                    $collection->getRenderedItem($child),
                    $collection->identify($child),
                    $child
                );
            }
        }

        return $this->renderRow($innerText, $collection->identify($container), $container);
    }
}
