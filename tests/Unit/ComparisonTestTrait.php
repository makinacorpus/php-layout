<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Render\DefaultIdentifierStrategy;
use MakinaCorpus\Layout\Render\GridRendererInterface;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemAType;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemBType;
use MakinaCorpus\Layout\Type\HorizontalContainerType;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;
use MakinaCorpus\Layout\Type\VerticalContainerType;

/**
 * Comparison test basics
 */
trait ComparisonTestTrait
{
    /**
     * @return ItemTypeRegistry
     */
    protected function createTypeRegistry(GridRendererInterface $gridRenderer) : ItemTypeRegistry
    {
        $aType = new ItemAType();
        $bType = new ItemBType();

        $vboxType = new VerticalContainerType($gridRenderer);
        $hboxType = new HorizontalContainerType($gridRenderer);

        $typeRegistry = new ItemTypeRegistry();
        $typeRegistry->registerType($aType);
        $typeRegistry->registerType($bType);
        $typeRegistry->registerType($vboxType);
        $typeRegistry->registerType($hboxType);

        return $typeRegistry;
    }

    /**
     * @return Renderer
     */
    protected function createRenderer(ItemTypeRegistry $typeRegistry) : Renderer
    {
        return new Renderer($typeRegistry, new DefaultIdentifierStrategy());
    }

    /**
     * Normalize XML for comparison
     *
     * @param string $input
     *
     * @return string
     */
    protected function normalizeXML(string $input) : string
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
