<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * This implementation is suitable for Symfony <= 3 and Drupal <= 8.
 */
class EditTokenArgumentValueResolver implements ArgumentValueResolverInterface
{
    private $context;

    /**
     * Default constructor
     *
     * @param Context $context
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
        return EditToken::class && $this->context->hasToken();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $this->context->getToken();
    }
}
