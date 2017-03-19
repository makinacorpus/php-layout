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
    private function createTypeRegistry(GridRendererInterface $gridRenderer) : ItemTypeRegistry
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
    private function createRenderer(ItemTypeRegistry $typeRegistry) : Renderer
    {
        return new Renderer($typeRegistry, new DefaultIdentifierStrategy());
    }
}
