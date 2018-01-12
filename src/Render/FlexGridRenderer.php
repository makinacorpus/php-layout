<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;

/**
 * Renders grids using flexbox CSS layout.
 *
 * You will need to add this to your CSS:
 * @code
 * .layout-container {
 *   display: flex;
 *   flex-flow: row;
 *   margin: 0 -15px;
 * }
 * .layout-container > div {
 *   position: relative;
 *   flex: auto;
 *   flex-basis: 0;
 *   padding-left: 15px;
 *   padding-right: 15px;
 * }
 * .layout-container > .layout-column-4 {
 *   flex: initial;
 *   width: 25%;
 * }
 * .layout-container > .layout-column-3 {
 *   flex: initial;
 *   width: 33%;
 * }
 * .layout-container > .layout-column-2 {
 *   flex: initial;
 *   width: 50%;
 * }
 * .layout-container > .layout-column-3-2 {
 *   flex: initial;
 *   width: 66%;
 * }
 * // This last one may save your user from having some kind of trouble
 * .layout-container img {
 *   max-width: 100%;
 * }
 * @endcode
 */
class FlexGridRenderer implements GridRendererInterface
{
    use HtmlInjectionTrait;

    /**
     * {@inheritdoc}
     */
    public function getColumnStyles() : array
    {
        return [
            ItemInterface::STYLE_DEFAULT => 'Automatic',
            'flext-4'   => "25%",
            'flext-3'   => "33%",
            'flext-2'   => "50%",
            'flext-3-2' => "66%",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, string $innerHtml, array $attributes = []) : string
    {
        if (isset($attributes['class'])) {
            $attributes['class'] .= 'layout-top';
        } else {
            $attributes['class'] = 'layout-top';
        }

        return '<div'.$this->renderAttributes($attributes).'>' . $innerHtml . '</div>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, string $innerHtml, array $attributes = []) : string
    {
        $columnClass = '';

        switch ($container->getStyle()) {
            case 'flext-4':
                $columnClass = 'layout-column-4';
                break;
            case 'flext-3':
                $columnClass = 'layout-column-3';
                break;
            case 'flext-2':
                $columnClass = 'layout-column-2';
                break;
            case 'flext-3-2':
                $columnClass = 'layout-column-3-2';
                break;
        }

        if ($columnClass) {
            if (isset($attributes['class'])) {
                $attributes['class'] .= $columnClass;
            } else {
                $attributes['class'] = $columnClass;
            }
        }

        return '<div'.$this->renderAttributes($attributes).'>'.$innerHtml.'</div>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, array $columnsHtml, array $attributes = []) : string
    {
        if (isset($attributes['class'])) {
            $attributes['class'] .= 'layout-container';
        } else {
            $attributes['class'] = 'layout-container';
        }

        return '<div'.$this->renderAttributes($attributes).'>'.implode("\n", $columnsHtml).'</div>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, string $innerHtml, int $position, array $attributes = []) : string
    {
        if ($attributes) {
            return $this->injectHtml($innerHtml, '', $attributes);
        }

        return $innerHtml;
    }
}
