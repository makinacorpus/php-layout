<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Render\DefaultIdentifierStrategy;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemAType;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemBType;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;
use MakinaCorpus\Layout\Render\GridRendererInterface;

/**
 * Comparison test basics
 */
trait ComparisonTestTrait
{
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
        return new Renderer($typeRegistry, $gridRenderer, new DefaultIdentifierStrategy());
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
