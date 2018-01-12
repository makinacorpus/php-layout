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
use MakinaCorpus\Layout\Render\EditRendererDecorator;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    private $editGridRenderer;
    private $renderer;
    private $testMode = false;
    private $typeRegistry;

    /**
     * Default constructor
     */
    public function __construct(EditRendererDecorator $editGridRenderer, ItemTypeRegistry $typeRegistry, Renderer $renderer)
    {
        $this->editGridRenderer = $editGridRenderer;
        $this->renderer = $renderer;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Toggle test mode: will return arrays instead of returning responses.
     */
    public function toggleTestMode(bool $toggle = true)
    {
        $this->testMode = $toggle;
    }

    /**
     * Get post parameter or die
     */
    private function getParamOrDie(Request $request, string $name) : string
    {
        if (null === ($value = $request->get($name))) {
            throw new NotFoundHttpException();
        }

        return $value;
    }

    /**
     * Load layout or die
     */
    protected function ensureLayout(EditToken $token, LayoutInterface $layout)
    {
        if (!$token->contains($layout->getId())) {
            throw new SecurityError(sprintf("layout %d is not temporary or not attached to token %s", $layout->getId(), $token->getToken()));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareResponse(Request $request, Context $context, EditToken $token)
    {
        // Force context to be set in AJAX queries in order for the rendering
        // to include all meta-information necessary.
        if ($request->isXmlHttpRequest()) {
            $this->editGridRenderer->setCurrentToken($context->getToken());
        }
    }

    /**
     * Handle edit controller response
     */
    protected function handleResponse(Request $request, array $ret)
    {
        if ($this->testMode) {
            return $ret;
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($ret);
        }

        // Handle redirect gracefully
        //   @todo the php-calista redirect router would be perfect...
        return new Response();
    }

    /**
     * Get allowed styles for item type and identifier
     */
    public function getAllowedStylesAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $itemId */)
    {
        $itemId = $this->getParamOrDie($request, 'itemId');

        $this->ensureLayout($token, $layout);
        $item = $layout->findItem($itemId);

        if ($item instanceof ContainerInterface) {
            if ($item instanceof ColumnContainer) {
                $allowedStyles = $this->renderer->getGridRenderer()->getColumnStyles();
            } else {
                $allowedStyles = ["default" => "default"];
            }
        } else {
            $allowedStyles = $this->typeRegistry->getType($item->getType())->getAllowedStylesFor($item);
        }

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true, 'current' => $item->getStyle(), 'styles'  => $allowedStyles]);
    }

    /**
     * Set option
     */
    public function setStyleAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $itemId, string $style = ItemInterface::STYLE_DEFAULT */)
    {
        $itemId = $this->getParamOrDie($request, 'itemId');
        $style = $request->get('style', ItemInterface::STYLE_DEFAULT);

        $this->ensureLayout($token, $layout);
        $item = $layout->findItem($itemId);
        $parent = $layout->findContainerOf($itemId);

        $item->setStyle($style);

        $context->getTokenStorage()->update($token->getToken(), $layout);

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true, 'output' => $this->renderer->renderItemIn($item, $parent, 0)]);
    }

    /**
     * Rerender an item or container
     */
    public function renderAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $itemId */)
    {
        $itemId = $this->getParamOrDie($request, 'itemId');

        $this->ensureLayout($token, $layout);
        $item = $layout->findItem($itemId);
        $parent = $layout->findContainerOf($itemId);

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true, 'output' => $this->renderer->renderItemIn($item, $parent, 0)]);
    }

    /**
     * Remove an item or container, and all its descendents
     */
    public function removeAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $itemId */)
    {
        $itemId = $this->getParamOrDie($request, 'itemId');

        $this->ensureLayout($token, $layout);
        $container = $layout->findContainerOf($itemId);

        if (!$container instanceof ContainerInterface) {
            throw new GenericError("you cannot remove items from a non-container");
        }

        /** @var \MakinaCorpus\Layout\Grid\ItemInterface $child */
        foreach ($container->getAllItems() as $position => $child) {
            if ($child->getStorageId() == $itemId) {
                if ($container instanceof HorizontalContainer) {
                    $container->removeColumnAt($position);
                } else {
                    $container->removeAt($position);
                }
                break;
            }
        }

        $context->getTokenStorage()->update($token->getToken(), $layout);

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true]);
    }

    /**
     * Add column container into another container
     */
    public function addColumnContainerAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $containerId, int $position = 0, int $columnCount = 2, string $style = ItemInterface::STYLE_DEFAULT */)
    {
        $containerId = $this->getParamOrDie($request, 'containerId');
        $position = $this->getParamOrDie($request, 'position');
        $columnCount = $this->getParamOrDie($request, 'columnCount');
        $style = $request->get('style', ItemInterface::STYLE_DEFAULT);

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

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true, 'output' => $this->renderer->render($horizontal)]);
    }

    /**
     * Add column to horizontal container
     */
    public function addColumnAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $containerId, int $position = 0 */)
    {
        $containerId = $this->getParamOrDie($request, 'containerId');
        $position = $this->getParamOrDie($request, 'position');

        $this->ensureLayout($token, $layout);
        $container = $layout->findContainer($containerId);

        if (!$container instanceof HorizontalContainer) {
            throw new GenericError("you cannot add columns into a non-horizontal container");
        }

        $column = $container->createColumnAt($position);

        $context->getTokenStorage()->update($token->getToken(), $layout);

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true, 'output' => $this->renderer->render($column)]);
    }

    /**
     * Add an item into another
     */
    public function addAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $containerId, string $itemType, string $itemId, int $position = 0, string $style = ItemInterface::STYLE_DEFAULT */)
    {
        $containerId = $this->getParamOrDie($request, 'containerId');
        $itemType = $this->getParamOrDie($request, 'itemType');
        $itemId = $this->getParamOrDie($request, 'itemId');
        $position = $this->getParamOrDie($request, 'position');
        $style = $request->get('style', ItemInterface::STYLE_DEFAULT);

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

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true, 'output' => $this->renderer->renderItemIn($item, $container, $position)]);
    }

    /**
     * Add an item from a container to any other container within the same layout
     */
    public function moveAction(Request $request, Context $context, EditToken $token, LayoutInterface $layout /*, int $containerId, int $itemId, int $newPosition */)
    {
        $containerId = $this->getParamOrDie($request, 'containerId');
        $itemId = $this->getParamOrDie($request, 'itemId');
        $newPosition = $this->getParamOrDie($request, 'newPosition');

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

        $this->prepareResponse($request, $context, $token);

        return $this->handleResponse($request, ['success' => true, 'output' => $this->renderer->renderItemIn($item, $container, $newPosition)]);
    }
}
