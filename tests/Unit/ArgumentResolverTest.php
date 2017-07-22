<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Controller\ArgumentResolver\ContextValueResolver;
use MakinaCorpus\Layout\Controller\ArgumentResolver\EditTokenValueResolver;
use MakinaCorpus\Layout\Controller\ArgumentResolver\LayoutValueResolver;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Covers argument resolvers
 */
class ArgumentResolverTest extends \PHPUnit_Framework_TestCase
{
    use ComparisonTestTrait;

    /**
     * Create an always negative result for our argument resolvers
     *s
     * @return ArgumentMetadata[]
     */
    private function createDumbArguments()
    {
        // Confusing names, confusing types
        return [
            new ArgumentMetadata('context', 'Context', false, false, false),
            new ArgumentMetadata('layout', 'Layout', false, false, false),
            new ArgumentMetadata('editToken', 'EditToken', false, false, false),
        ];
    }

    /**
     * Tests the context value resolver
     */
    public function testContextValueResolver()
    {
        $context  = $this->createContext();
        $resolver = new ContextValueResolver($context);
        $request  = new Request();
        $argument = new ArgumentMetadata('any_name_is_good', Context::class, false, false, false);

        foreach ($this->createDumbArguments() as $negativeArgument) {
            $this->assertFalse($resolver->supports($request, $negativeArgument));
        }

        $this->assertTrue($resolver->supports($request, $argument));

        $count = 0;
        foreach ($resolver->resolve($request, $argument) as $value) {
            $this->assertSame($context, $value);
            $count++;
        }
        $this->assertSame(1, $count);
    }

    /**
     * Tests the edit token value resolver
     */
    public function testEditTokenValueResolver()
    {
        $context  = $this->createContext();
        $resolver = new EditTokenValueResolver($context);
        $request  = new Request();
        $argument = new ArgumentMetadata('any_name_is_good', EditToken::class, false, false, false);

        foreach ($this->createDumbArguments() as $negativeArgument) {
            $this->assertFalse($resolver->supports($request, $negativeArgument));
        }

        // EditToken cannot resolve a token without a token in context
        $this->assertFalse($resolver->supports($request, $argument));

        try {
            foreach ($resolver->resolve($request, $argument) as $value) {
                $this->fail();
            }
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        // And now it will resolve
        $editToken = $context->createEditToken();
        $this->assertTrue($resolver->supports($request, $argument));

        $count = 0;
        foreach ($resolver->resolve($request, $argument) as $value) {
            $this->assertSame($editToken, $value);
            $count++;
        }
        $this->assertSame(1, $count);
    }

    /**
     * Tests the layout vale resolver
     */
    public function testLayoutValueResolver()
    {
        $context  = $this->createContext();
        $resolver = new LayoutValueResolver($context);
        $request  = new Request();
        $argument = new ArgumentMetadata('any_name_is_good', LayoutInterface::class, false, false, false);

        foreach ($this->createDumbArguments() as $negativeArgument) {
            $this->assertFalse($resolver->supports($request, $negativeArgument));
        }

        // Creates layouts for testing
        $layout1 = $context->getLayoutStorage()->create();
        $layout2 = $context->getLayoutStorage()->create();

        // Create a token, but add only one of them into it
        $context->addLayoutList([$layout1->getId(), $layout2->getId()]);
        $context->toggleEditable([$layout1->getId()], false);
        $context->toggleEditable([$layout2->getId()], true);
        $context->createEditToken([$layout2->getId()]);

        // First argument will be the first layout, should return the permanent one
        // It should resolve with both query and attributes
        $request = new Request(['any_name_is_good' => $layout1->getId()]);
        $this->assertTrue($resolver->supports($request, $argument));

        $count = 0;
        foreach ($resolver->resolve($request, $argument) as $value) {
            // Storage loads immutable things, we cannot check for the same
            // instance it will always fail
            $this->assertSame($layout1->getId(), $value->getId());
            $count++;
        }
        $this->assertSame(1, $count);

        $request = new Request([], [], ['any_name_is_good' => $layout1->getId()]);
        $this->assertTrue($resolver->supports($request, $argument));

        $count = 0;
        foreach ($resolver->resolve($request, $argument) as $value) {
            // Storage loads immutable things, we cannot check for the same
            // instance it will always fail
            $this->assertSame($layout1->getId(), $value->getId());
            $count++;
        }
        $this->assertSame(1, $count);

        // Second argument will be the second layout, should return the temporary one
        // It should resolve with both query and attributes
        $request = new Request(['any_name_is_good' => $layout2->getId()]);
        $this->assertTrue($resolver->supports($request, $argument));

        $count = 0;
        foreach ($resolver->resolve($request, $argument) as $value) {
            $this->assertNotSame($layout2, $value);
            $this->assertSame($layout2->getId(), $value->getId());
            $count++;
        }
        $this->assertSame(1, $count);

        $request = new Request([], [], ['any_name_is_good' => $layout2->getId()]);
        $this->assertTrue($resolver->supports($request, $argument));

        $count = 0;
        foreach ($resolver->resolve($request, $argument) as $value) {
            $this->assertNotSame($layout2, $value);
            $this->assertSame($layout2->getId(), $value->getId());
            $count++;
        }
        $this->assertSame(1, $count);

        // Third argument will be a non existing layout, should throw exception
        // It should fail with both query and attributes
        $request = new Request(['any_name_is_good' => 127]);
        $this->assertTrue($resolver->supports($request, $argument));

        try {
            foreach ($resolver->resolve($request, $argument) as $value) {
                $this->fail();
            }
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        $request = new Request([], [], ['any_name_is_good' => 127]);
        $this->assertTrue($resolver->supports($request, $argument));

        try {
            foreach ($resolver->resolve($request, $argument) as $value) {
                $this->fail();
            }
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
    }
}

