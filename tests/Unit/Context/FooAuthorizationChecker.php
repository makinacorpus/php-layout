<?php

namespace MakinaCorpus\Layout\Tests\Unit\Context;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FooAuthorizationChecker implements AuthorizationCheckerInterface
{
    private $callCount = 0;

    public function isGranted($attributes, $object = null)
    {
        $this->callCount++;

        return false;
    }

    public function getCallCount()
    {
        return $this->callCount;
    }
}
