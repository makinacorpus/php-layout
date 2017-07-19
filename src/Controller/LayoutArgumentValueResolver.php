<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Storage\LayoutStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * This implementation is suitable for Symfony <= 3 and Drupal <= 8.
 *
 * Beware that this implementation will substitute layouts with temporary ones
 * as soon as the layout exists in current context.
 */
class LayoutArgumentValueResolver implements ArgumentValueResolverInterface
{
    private $context;
    private $layoutStorage;

    /**
     * Default constructor
     *
     * @param Context $context
     * @param LayoutStorageInterface $layoutStorage
     */
    public function __construct(Context $context, LayoutStorageInterface $layoutStorage)
    {
        $this->context = $context;
        $this->layoutStorage = $layoutStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $name = $argument->getName();

        return LayoutInterface::class === $argument->getType() && ($request->query->has($name) || $request->request->has($name) || $request->attributes->has($name));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $name = $argument->getName();
        $id = $request->attributes->get($name, $request->get($name));

        if (!$id) {
            return;
        }

        if ($this->context->has($id)) {
            yield $this->context->getLayout($id);
        }

        yield $this->layoutStorage->load($id);
    }
}
