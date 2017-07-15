<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Error\GenericError;
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
        return LayoutInterface::class === $argument->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $layoutId = $request->get($argument->getName());

        if (!$layoutId) {
            return;
        }

        if ($this->context->hasToken()) {
            try {
                $token = $this->context->getCurrentToken();

                yield $this->context->getTokenStorage()->load($token->getToken(), $layoutId);

            } catch (GenericError $e) {
                // In case we have any error, just let the algorithm continue
                // with a normal load attempt: it's the controllers responsability
                // to ensure that layout is contained by the token
            }
        }

        yield $this->layoutStorage->load($layoutId);
    }
}
