<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Storage\TokenLayoutStorageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * This implementation is suitable for Symfony <= 3 and Drupal <= 8.
 */
class EditTokenArgumentValueResolver implements ArgumentValueResolverInterface
{
    private $tokenStorage;

    /**
     * Default constructor
     *
     * @param TokenLayoutStorageInterface $tokenStorage
     */
    public function __construct(TokenLayoutStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $name = $argument->getName();

        return EditToken::class === $argument->getType() && ($request->query->has($name) || $request->request->has($name));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $tokenString = $request->get($argument->getName());

        if ($tokenString) {
            yield $this->tokenStorage->loadToken($tokenString);
        }

        throw new \InvalidArgumentException();
    }
}
