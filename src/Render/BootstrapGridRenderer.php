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
    use HtmlInjectionTrait;

    /**
     * Compute columns sizes
     *
     * I am not proud of this method, but it should work.
     *
     * @param HorizontalContainer $container
     *
     * @return string[]
     *   Indexes are column positions, values are computed classes
     */
    private function computeSizes(HorizontalContainer $container)
    {
        $ret = [];

        $autoMd = [];
        $autoSm = [];
        $restMd = 12;
        $restSm = 12;

        foreach ($container->getAllItems() as $position => $column) {

            $style = $column->getStyle();
            $class = [];

            if (false === strpos($style, '/')) {
                $autoMd[] = $position;
                $autoSm[] = $position;
                continue;
            }

            list($md, $sm) = explode('/', $style);
            $md = (int)$md;
            $sm = (int)$sm;

            if (!$sm) {
                $autoSm[] = $position;
            } else {
                $class[] = 'col-sm-' . $sm;
                $restSm -= $sm;
            }

            if (!$md) {
                $autoMd[] = $position;
            } else {
                $class[] = 'col-md-' . $md;
                $restMd -= $md;
            }

            $ret[$position] = $class;
        }

        // If there is auto columns, just count, divise and set
        // @todo floor'ed size might leave 1 or 2 empty space, make it fit somehow

        if ($autoMd) {
            if ($restMd < count($autoMd)) {
                $size = 6; // sorry, nothing can be done here
            } else {
                $size = floor($restMd / count($autoMd));
            }
            foreach ($autoMd as $position) {
                $ret[$position][] = 'col-md-' . $size;
            }
        }

        if (12 !== $restSm && $autoSm) {
            if ($restSm < count($autoSm)) {
                $size = 6; // sorry, nothing can be done here
            } else {
                $size = floor($restSm / count($autoSm));
            }
            foreach ($autoMd as $position) {
                $ret[$position][] = 'col-sm-' . $size;
            }
        }

        return array_map(function ($value) { return implode(' ', $value); }, $ret);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnStyles() : array
    {
        return [
            ItemInterface::STYLE_DEFAULT => 'Automatic',
            // col-md-9
            '9'       => "All: 75%",
            '9/6'     => "Desktop: 75%, Tablet: 50%",
            '9/12'    => "Desktop: 75%, Tablet: 100%",
            // col-md-6
            '6'       => "All: 50%",
            '6/12'    => "Desktop: 50%, Tablet: 100%",
            // col-md-4
            '4'       => "All: 33%",
            '4/6'     => "Desktop: 33%, Tablet: 50%",
            '4/12'    => "Desktop: 33%, Tablet: 100%",
            // col-md-3
            '3'       => "All: 25%",
            '3/6'     => "Desktop: 25%, Tablet: 50%",
            '3/12'    => "Desktop: 25%, Tablet: 100%",
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, string $innerHtml, array $attributes = []) : string
    {
        $putContainer = false;

        if (!$container->getOption('container-none')) {
            $putContainer = true;

            if ($container->getOption('container-fluid')) {
                $containerClass = 'container-fluid';
            } else {
                $containerClass = 'container';
            }
        }

        if ($putContainer) {
            if (isset($attributes['class'])) {
                $attributes['class'] .= ' col-md-12';
            } else {
                $attributes['class'] = 'col-md-12';
            }
        }

        if ($putContainer) {
            return '<div class="'.$containerClass.'"><div class="row"><div'.$this->renderAttributes($attributes).'>' . $innerHtml . '</div></div></div>';
        } else {
            return '<div'.$this->renderAttributes($attributes).'>' . $innerHtml . '</div>';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, string $innerHtml, array $attributes = []) : string
    {
        return '<div'.$this->renderAttributes($attributes).'>'.$innerHtml.'</div>';
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, array $columnsHtml, array $attributes = []) : string
    {
        $innerText = '';

        if (!$container->isEmpty()) {
            $styles = $this->computeSizes($container);

            foreach ($columnsHtml as $position => $singleColumnHtml) {
                if (empty($styles[$position])) {
                    $innerText .= $singleColumnHtml;
                } else {
                    $innerText .= $this->injectHtml($columnsHtml[$position], '', ['class' => $styles[$position]]);
                }
            }
        }

        if (isset($attributes['class'])) {
            $attributes['class'] .= ' row';
        } else {
            $attributes['class'] = 'row';
        }

        return '<div'.$this->renderAttributes($attributes).'>'.$innerText.'</div>';
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
