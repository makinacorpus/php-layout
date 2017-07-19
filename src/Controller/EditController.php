<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Error\OutOfBoundsError;
use MakinaCorpus\Layout\Error\SecurityError;
use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;

/**
 * Controller that should be suitable with most frameworks.
 *
 * Your framework must allow to preload items given as parameters, for example
 * a Symfony app would use a param converter to inject the correct EditToken
 * into the actions methods.
 *
 * This controller works exclusively with storage identifiers.
 *
 * It does not handle security by itself, the previous parameter converter
 * should proceed with all additional security checks.
 *
 * All methods will return an array, it's up to you to extend or decorate
 * this controller in order to return the output you wish.
 */
class EditController
{
    /**
     * @var ItemTypeRegistry
     */
    private $typeRegistry;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * Default constructor
     *
     * @param ItemTypeRegistry $typeRegistry
     * @param Renderer $renderer
     */
    public function __construct(ItemTypeRegistry $typeRegistry, Renderer $renderer)
    {
        $this->typeRegistry = $typeRegistry;
        $this->renderer = $renderer;
    }

    /**
     * Load layout or die
     *
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     */
    public function ensureLayout(EditToken $token, LayoutInterface $layout)
    {
        if (!$token->contains($layout->getId())) {
            throw new SecurityError(sprintf("layout %d is not temporary or not attached to token %s", $layout->getId(), $token->getToken()));
        }
    }

    /**
     * Remove an item or container, and all its descendents
     *
     * @param Context $context
     *   Current context
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     */
    public function removeAction(Context $context, EditToken $token, LayoutInterface $layout, int $itemId)
    {
        $this->ensureLayout($token, $layout);
        $container = $layout->findContainerOf($itemId);

        if (!$container instanceof TopLevelContainer && !$container instanceof ColumnContainer) {
            throw new GenericError("you cannot remove items from a non-vertical container");
        }

        /** @var \MakinaCorpus\Layout\Grid\ItemInterface $child */
        foreach ($container->getAllItems() as $position => $child) {
            if ($child->getStorageId() == $itemId) {
                $container->removeAt($position);
                break;
            }
        }

        $context->getTokenStorage()->update($token->getToken(), $layout);

        return ['success' => true];
    }

    /**
     * Add column container into another container
     *
     * @param Context $context
     *   Current context
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     * @param int $containerId
     *   Container storage identifier
     * @param int $position
     *   Position
     * @param int $columnCount
     *   Default column count
     * @param string $style
     *   Item style, if none use default
     */
    public function addColumnContainerAction(Context $context, EditToken $token, LayoutInterface $layout, int $containerId, int $position = 0, int $columnCount = 2, string $style = ItemInterface::STYLE_DEFAULT)
    {
        $this->ensureLayout($token, $layout);

        if ($containerId) {
            $container = $layout->findContainer($containerId);
        } else {
            $container = $layout->getTopLevelContainer();
        }

        $horizontal = new HorizontalContainer();
        $horizontal->setStyle($style);
        $horizontal->setLayoutId($layout->getId());

        if (!$container instanceof TopLevelContainer && !$container instanceof ColumnContainer) {
            throw new GenericError("you cannot add items into a non-vertical container");
        }
        if (1 > $columnCount || 100 < $columnCount) {
            throw new OutOfBoundsError(sprintf("%d: column number out of bounds, must be between 1 and 100"));
        }

        for ($i = 0; $i < $columnCount; ++$i) {
            $horizontal->appendColumn();
        }

        $container->addAt($horizontal, $position);

        $context->getTokenStorage()->update($token->getToken(), $layout);

        return ['success' => true, 'output' => $this->renderer->render($horizontal)];
    }

    /**
     * Add column to horizontal container
     *
     * @param Context $context
     *   Current context
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     * @param int $containerId
     *   Container storage identifier
     * @param int $position
     *   Position
     */
    public function addColumnAction(Context $context, EditToken $token, LayoutInterface $layout, int $containerId, int $position = 0)
    {
        $this->ensureLayout($token, $layout);
        $container = $layout->findContainer($containerId);

        if (!$container instanceof HorizontalContainer) {
            throw new GenericError("you cannot add columns into a non-horizontal container");
        }

        $column = $container->createColumnAt($position);

        $context->getTokenStorage()->update($token->getToken(), $layout);

        return ['success' => true, 'output' => $this->renderer->render($column)];
    }

    /**
     * Remove column to horizontal container
     *
     * @param Context $context
     *   Current context
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     * @param int $containerId
     *   Container storage identifier
     * @param int $position
     *   Position
     */
    public function removeColumnAction(Context $context, EditToken $token, LayoutInterface $layout, int $containerId, int $position = 0)
    {
        $this->ensureLayout($token, $layout);
        $container = $layout->findContainer($containerId);

        if (!$container instanceof HorizontalContainer) {
            throw new GenericError("you cannot add columns into a non-horizontal container");
        }

        $container->removeColumnAt($position);

        $context->getTokenStorage()->update($token->getToken(), $layout);

        return ['success' => true];
    }

    /**
     * Add an item into another
     *
     * @param Context $context
     *   Current context
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     * @param int $containerId
     *   Container storage identifier
     * @param string $itemType
     *   Item type
     * @param string $itemId
     *   Item identifier
     * @param int $position
     *   Position
     * @param string $style
     *   Item style, if none use default
     */
    public function addAction(Context $context, EditToken $token, LayoutInterface $layout, int $containerId, string $itemType, string $itemId, int $position = 0, string $style = ItemInterface::STYLE_DEFAULT)
    {
        $this->ensureLayout($token, $layout);
        $item = $this->typeRegistry->getType($itemType, false)->create($itemId, $style);
        $item->setLayoutId($layout->getId());

        if ($containerId) {
            $container = $layout->findContainer($containerId);
        } else {
            $container = $layout->getTopLevelContainer();
        }

        if ($item instanceof ContainerInterface) {
            throw new GenericError("you cannot add a container into a container");
        }
        if (!$container instanceof TopLevelContainer && !$container instanceof ColumnContainer) {
            throw new GenericError("you cannot add items into a non-vertical container");
        }

        $container->addAt($item, $position);

        $context->getTokenStorage()->update($token->getToken(), $layout);

        return ['success' => true, 'output' => $this->renderer->renderItemIn($item, $container, $position)];
    }

    /**
     * Add an item from a container to any other container within the same layout
     *
     * @param Context $context
     *   Current context
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     * @param string $itemType
     *   Item type
     * @param string $itemId
     *   Item identifier
     * @param int $position
     *   Position
     */
    public function moveAction(Context $context, EditToken $token, LayoutInterface $layout, int $containerId, int $itemId, int $newPosition)
    {
        $this->ensureLayout($token, $layout);

        $container  = $layout->findContainer($containerId);
        $parent     = $layout->findContainerOf($itemId);
        $item       = null;
        $position   = null;

        if ($containerId) {
            $container = $layout->findContainer($containerId);
        } else {
            $container = $layout->getTopLevelContainer();
        }

        /** @var \MakinaCorpus\Layout\Grid\ItemInterface $item */
        foreach ($parent->getAllItems() as $index => $child) {
            if ($child->getStorageId() == $itemId) {
                $item = $child;
                $position = $index;
                break;
            }
        }

        if ($item instanceof ColumnContainer) {
            throw new GenericError("you cannot move a column");
        }
        if (!$parent instanceof TopLevelContainer && !$parent instanceof ColumnContainer) {
            // @codeCoverageIgnoreStart
            // This is an impossible use case with non-broken data
            throw new GenericError("you cannot move items from a non-vertical container");
            // @codeCoverageIgnoreEnd
        }
        if (!$container instanceof TopLevelContainer && !$container instanceof ColumnContainer) {
            throw new GenericError("you cannot move items into a non-vertical container");
        }

        $parent->removeAt($position);
        $container->addAt($item, $newPosition);
        $item->toggleUpdateStatus(true);

        $context->getTokenStorage()->update($token->getToken(), $layout);

        return ['success' => true, 'output' => $this->renderer->renderItemIn($item, $container, $newPosition)];
    }

    /**
     * Add an item from a position to another position
     *
     * @param Context $context
     *   Current context
     * @param EditToken $token
     *   Current edit context
     * @param LayoutInterface $layout
     *   Layout
     * @param string $itemType
     *   Item type
     * @param string $itemId
     *   Item identifier
     * @param int $position
     *   Position
     */
    public function moveOutsideAction(Context $context, EditToken $token)
    {
        throw new GenericError("this is not implemented yet");
    }
}
