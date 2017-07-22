<?php

namespace MakinaCorpus\Layout\Controller\ArgumentResolver;

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
class LayoutValueResolver implements ArgumentValueResolverInterface
{
    private $context;

    /**
     * Default constructor
     *
     * @param Context $context
     * @param LayoutStorageInterface $layoutStorage
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
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

        if ($this->context->hasLayout($id)) {
            yield $this->context->getLayout($id);
        } else {
            yield $this->context->getLayoutStorage()->load($id);
        }
    }
}
