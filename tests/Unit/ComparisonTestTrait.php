<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Context\DefaultTokenGenerator;
use MakinaCorpus\Layout\Render\GridRendererInterface;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Tests\Unit\Context\FooAuthorizationChecker;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemAType;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemBType;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestLayoutStorage;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestTokenLayoutStorage;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Comparison test basics
 */
trait ComparisonTestTrait
{
    /**
     * Create an empty testing token
     *
     * @return Context
     */
    protected function createContext() : Context
    {
        $storage = new TestLayoutStorage();
        $tokenStorage = new TestTokenLayoutStorage();
        $tokenGenerator = new DefaultTokenGenerator();
        $context = new Context($storage, $tokenStorage, new FooAuthorizationChecker(), new EventDispatcher(), $tokenGenerator);

        return $context;
    }

    /**
     * @return ItemTypeRegistry
     */
    protected function createTypeRegistry() : ItemTypeRegistry
    {
        $aType = new ItemAType();
        $bType = new ItemBType();

        $typeRegistry = new ItemTypeRegistry();
        $typeRegistry->registerType($aType);
        $typeRegistry->registerType($bType);

        return $typeRegistry;
    }

    /**
     * @return Renderer
     */
    protected function createRenderer(ItemTypeRegistry $typeRegistry, GridRendererInterface $gridRenderer) : Renderer
    {
        return new Renderer($typeRegistry, $gridRenderer);
    }

    /**
     * Normalize XML for comparison
     *
     * @param string $input
     * @param bool $assertIdAreUnique
     *
     * @return string
     */
    protected function normalizeXML(string $input, bool $assertIdAreUnique = true) : string
    {
        return preg_replace('/\s+/', '', $input);
    }

    /**
     * Asserts that two variables are the same rendered output
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    protected function assertSameRenderedGrid($expected, $actual, $message = '')
    {
        $this->assertSame($this->normalizeXML($expected), $this->normalizeXML($actual), $message = '');
    }
}
