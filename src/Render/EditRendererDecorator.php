<?php

namespace MakinaCorpus\Layout\Render;

use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;

/**
 * Decorates another rendererd, and injects necessary data attributes for JS UI.
 */
class EditRendererDecorator implements GridRendererInterface
{
    use HtmlInjectionTrait;

    /**
     * @var GridRendererInterface
     */
    private $nested;

    /**
     * @var EditToken
     */
    private $token;

    /**
     * Default constructor
     *
     * @param GridRendererInterface $nested
     */
    public function __construct(GridRendererInterface $nested)
    {
        $this->nested = $nested;
    }

    /**
     * Is given item in current token
     */
    private function isTemporary(ItemInterface $item): bool
    {
        return $this->token && $this->token->contains($item->getLayoutId());
    }

    /**
     * Allow changing context
     *
     * @todo find another way
     *
     * @param EditToken $token
     */
    public function setCurrentToken(EditToken $token)
    {
        $this->token = $token;
    }

    /**
     * Drop current token
     */
    public function dropToken()
    {
        $this->token = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnStyles() : array
    {
        return $this->nested->getColumnStyles();
    }

    /**
     * {@inheritdoc}
     */
    public function renderTopLevelContainer(TopLevelContainer $container, string $innerHtml, array $attributes = []) : string
    {
        if ($this->isTemporary($container)) {
            $attributes['data-id'] = $container->getGridIdentifier();
            $attributes['data-token'] = $this->token->getToken();
            $attributes['data-layout'] = '';
            $attributes['data-container'] = 'layout';
            $addition = '';
        } else {
            $addition = '';
        }

        return $this->nested->renderTopLevelContainer($container, $addition . $innerHtml, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function renderColumnContainer(ColumnContainer $container, string $innerHtml, array $attributes = []) : string
    {
        if ($this->isTemporary($container)) {
            $attributes['data-id'] = $container->getGridIdentifier();
            $attributes['data-container'] = 'vbox';
            $addition = '';
        } else {
            $addition = '';
        }

        return $this->nested->renderColumnContainer($container, $addition . $innerHtml, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function renderHorizontalContainer(HorizontalContainer $container, array $columnsHtml, array $attributes = []) : string
    {
        if ($this->isTemporary($container)) {
            $attributes['data-id'] = $container->getGridIdentifier();
            $attributes['data-container'] = 'hbox';
            $attributes['data-readonly'] = '1';
            $addition = '';
        } else {
            $addition = '';
        }

        return $this->injectHtml(
            $this->nested->renderHorizontalContainer($container, $columnsHtml, $attributes),
            $addition
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderItem(ItemInterface $item, ContainerInterface $parent, string $innerHtml, int $position, array $attributes = []) : string
    {
        // Some implementations will re-use this method for rendering columns
        // or containers, case in which we must leave it untouched
        if (!$this->isTemporary($item) || $item instanceof ContainerInterface) {
            return $this->nested->renderItem($item, $parent, $innerHtml, $position);
        }

        $attributes['data-item-id'] = $item->getId();
        $attributes['data-item-type'] = $item->getType();
        $attributes['data-id'] = $item->getGridIdentifier();

        $rendered = $this->nested->renderItem($item, $parent, $innerHtml, $position);

        if (!$rendered) {
            $attributes['class'] = 'text-danger';
            $rendered = '<p class="text-danger">'.t("Broken or missing item").'</p>';
        }

        $addition = '';

        // Items are a very special case, they always should be contained into
        // an extra div, to ensure that we don't leak in the final content
        return '<div'.$this->renderAttributes($attributes).'>'.$addition.$innerHtml.'</div>';
    }
}
