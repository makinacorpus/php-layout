<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Error\OutOfBoundsError;
use MakinaCorpus\Layout\Error\SecurityError;
use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\VerticalContainer;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Storage\TokenLayoutStorageInterface;
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
     * @var TokenLayoutStorageInterface
     */
    private $storage;

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
     * @param TokenLayoutStorageInterface $storage
     * @param ItemTypeRegistry $typeRegistry
     * @param Renderer $renderer
     */
    public function __construct(TokenLayoutStorageInterface $storage, ItemTypeRegistry $typeRegistry, Renderer $renderer)
    {
        $this->storage = $storage;
        $this->typeRegistry = $typeRegistry;
        $this->renderer = $renderer;
    }

    /**
     * Load layout or die
     *
     * @param string $tokenString
     * @param int $layoutId
     *
     * @return LayoutInterface
     */
    private function loadLayoutOrDie(string $tokenString, int $layoutId) : LayoutInterface
    {
        $token  = $this->storage->loadToken($tokenString);
        $layout = $this->storage->load($token->getToken(), $layoutId);

        if (!$token->contains($layout)) {
            throw new SecurityError(sprintf("%d layout is not attached to token %s", $layoutId, $tokenString));
        }

        return $layout;
    }

    /**
     * Remove an item or container, and all its descendents
     *
     * @param string $tokenString
     */
    public function removeAction(string $tokenString, int $layoutId, int $itemId)
    {
        $layout     = $this->loadLayoutOrDie($tokenString, $layoutId);
        $container  = $layout->findContainerOf($itemId);

        if (!$container instanceof VerticalContainer) {
            throw new GenericError("you cannot remove items from a non-vertical container");
        }

        /** @var \MakinaCorpus\Layout\Grid\ItemInterface $child */
        foreach ($container->getAllItems() as $position => $child) {
            if ($child->getStorageId() == $itemId) {
                $container->removeAt($position);
                break;
            }
        }

        $this->storage->update($tokenString, $layout);

        return ['success' => true];
    }

    /**
     * Add column container into another container
     *
     * @param EditToken $token
     *   Current edit context
     * @param int $layoutId
     *   Layout identifier
     * @param int $containerId
     *   Container storage identifier
     * @param int $position
     *   Position
     * @param int $columnCount
     *   Default column count
     * @param string $style
     *   Item style, if none use default
     */
    public function addColumnContainerAction(string $tokenString, int $layoutId, int $containerId, int $position = 0, int $columnCount = 2, string $style = ItemInterface::STYLE_DEFAULT)
    {
        $layout     = $this->loadLayoutOrDie($tokenString, $layoutId);
        $container  = $layout->findContainer($containerId);

        $horizontal = new HorizontalContainer();
        $horizontal->setStyle($style);

        if (!$container instanceof VerticalContainer) {
            throw new GenericError("you cannot add items into a non-vertical container");
        }
        if (1 > $columnCount || 100 < $columnCount) {
            throw new OutOfBoundsError(sprintf("%d: column number out of bounds, must be between 1 and 100"));
        }

        for ($i = 0; $i < $columnCount; ++$i) {
            $horizontal->appendColumn();
        }

        $container->addAt($horizontal, $position);

        $this->storage->update($tokenString, $layout);

        return ['success' => true, 'output' => $this->renderer->render($horizontal)];
    }

    /**
     * Add column to horizontal container
     *
     * @param EditToken $token
     *   Current edit context
     * @param int $layoutId
     *   Layout identifier
     * @param int $containerId
     *   Container storage identifier
     * @param int $position
     *   Position
     */
    public function addColumnAction(string $tokenString, int $layoutId, int $containerId, int $position = 0)
    {
        $layout     = $this->loadLayoutOrDie($tokenString, $layoutId);
        $container  = $layout->findContainer($containerId);

        if (!$container instanceof HorizontalContainer) {
            throw new GenericError("you cannot add columns into a non-horizontal container");
        }

        $column = $container->createColumnAt($position);

        $this->storage->update($tokenString, $layout);

        return ['success' => true, 'output' => $this->renderer->render($column)];
    }

    /**
     * Remove column to horizontal container
     *
     * @param EditToken $token
     *   Current edit context
     * @param int $layoutId
     *   Layout identifier
     * @param int $containerId
     *   Container storage identifier
     * @param int $position
     *   Position
     */
    public function removeColumnAction(string $tokenString, int $layoutId, int $containerId, int $position = 0)
    {
        $layout     = $this->loadLayoutOrDie($tokenString, $layoutId);
        $container  = $layout->findContainer($containerId);

        if (!$container instanceof HorizontalContainer) {
            throw new GenericError("you cannot add columns into a non-horizontal container");
        }

        $container->removeColumnAt($position);

        $this->storage->update($tokenString, $layout);

        return ['success' => true];
    }

    /**
     * Add an item into another
     *
     * @param EditToken $token
     *   Current edit context
     * @param int $layoutId
     *   Layout identifier
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
    public function addAction(string $tokenString, int $layoutId, int $containerId, string $itemType, string $itemId, int $position = 0, string $style = ItemInterface::STYLE_DEFAULT)
    {
        $layout     = $this->loadLayoutOrDie($tokenString, $layoutId);
        $container  = $layout->findContainer($containerId);
        $item       = $this->typeRegistry->getType($itemType, false)->create($itemId, $style);

        if ($item instanceof ContainerInterface) {
            throw new GenericError("you cannot add a container into a container");
        }
        if (!$container instanceof VerticalContainer) {
            throw new GenericError("you cannot add items into a non-vertical container");
        }

        $container->addAt($item, $position);

        $this->storage->update($tokenString, $layout);

        return ['success' => true, 'output' => $this->renderer->render($item)];
    }

    /**
     * Add an item from a container to any other container within the same layout
     *
     * @param EditToken $token
     *   Current edit context
     * @param int $containerId
     *   Container storage identifier
     * @param string $itemType
     *   Item type
     * @param string $itemId
     *   Item identifier
     * @param int $position
     *   Position
     */
    public function moveAction(string $tokenString, int $layoutId, int $containerId, int $itemId, int $newPosition)
    {
        $layout     = $this->loadLayoutOrDie($tokenString, $layoutId);
        $container  = $layout->findContainer($containerId);
        $parent     = $layout->findContainerOf($itemId);
        $item       = null;
        $position   = null;

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
        if (!$parent instanceof VerticalContainer) {
            // @codeCoverageIgnoreStart
            // This is an impossible use case with non-broken data
            throw new GenericError("you cannot move items from a non-vertical container");
            // @codeCoverageIgnoreEnd
        }
        if (!$container instanceof VerticalContainer) {
            throw new GenericError("you cannot move items into a non-vertical container");
        }

        $parent->removeAt($position);
        $container->addAt($item, $newPosition);

        $this->storage->update($tokenString, $layout);

        return ['success' => true];
    }

    /**
     * Add an item from a position to another position
     *
     * @param EditToken $token
     *   Current edit context
     * @param int $containerId
     *   Container storage identifier
     * @param string $itemType
     *   Item type
     * @param string $itemId
     *   Item identifier
     * @param int $position
     *   Position
     */
    public function moveOutsideAction(string $tokenString)
    {
        throw new GenericError("this is not implemented yet");
    }
}
