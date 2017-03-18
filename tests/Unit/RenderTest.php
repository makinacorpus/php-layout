<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Container\BootstrapGridRenderer;
use MakinaCorpus\Layout\Container\HorizontalContainerType;
use MakinaCorpus\Layout\Container\VerticalContainerType;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\VerticalContainer;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemAType;
use MakinaCorpus\Layout\Tests\Unit\Render\ItemBType;
use MakinaCorpus\Layout\Tests\Unit\Render\XmlGridRenderer;
use MakinaCorpus\Layout\Type\ItemTypeRegistry;

/**
 * Render test, ensures the bottom-top rendering of elements
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Normalize XML for comparison
     *
     * @param string $input
     *
     * @return string
     */
    private function normalizeXML(string $input) : string
    {
        return preg_replace('/\s+/', '', $input);
    }

    /**
     * Tests the item base class
     */
    public function testComplexScenario()
    {
        /**
         *             TOP LEVEL
         * +-----------------------------+
         * | 2 columns, with nested:     |
         * |   C1                        |
         * |   C11           C12         |
         * | +-----------+-------------+ |
         * | |           |  C2         | |
         * | |           |  C21  C22   | |
         * | |           | +----+----+ | |
         * | | A1        | | A2 | B3 | | |
         * | | B4        | | A5 |    | | |
         * | |           | +----+----+ | |
         * | +-----+-----+-------------+ |
         * |                             |
         * | 3 columns:                  |
         * |   C3                        |
         * |   C31   C32      C33        |
         * | +------+------+-----------+ |
         * | | A6   | B7   | B8        | |
         * | | A9   | B10  | B11       | |
         * | |      |      | A1*       | |
         * | +------+------+-----------+ |
         * |                             |
         * |  A12                        |
         * |  *B7                        |
         * +-----------------------------+
         *
         * C: Container
         * A: Item of type A
         * B: Item of type B
         * *: Duplicated item
         */

        // This is pseudo XML reprensentation of what we are waiting for:
        $representation = <<<EOT
<vertical id="top-level">
    <horizontal id="C1">
        <column id="C11">
            <item id="A1" />
            <item id="B4" />
        </column>
        <column id="C12">
            <horizontal id="C2">
                <column id="C21">
                    <item id="A2" />
                    <item id="A5" />
                </column>
                <column id="C22">
                    <item id="B3" />
                </column>
            </horizontal>
        </column>
    </horizontal>
    <horizontal id="C3">
        <column id="C31">
            <item id="A6" />
            <item id="A9" />
        </column>
        <column id="C32">
            <item id="B7" />
            <item id="B10" />
        </column>
        <column id="C33">
            <item id="B8" />
            <item id="B11" />
            <item id="A1" />
        </column>
    </horizontal>
    <item id="A12" />
    <item id="B7" />
</vertical>
EOT;
        // Create types
        $aType = new ItemAType();
        $bType = new ItemBType();

        // Place a top level container and build layout (no items)
        $topLevel = new VerticalContainer('top-level');
        $c1 = new HorizontalContainer('C1');
        $topLevel->append($c1);
        $c11 = $c1->appendColumn('C11');
        $c12 = $c1->appendColumn('C12');
        $c2 = new HorizontalContainer('C2');
        $c12->append($c2);
        $c21 = $c2->appendColumn('C21');
        $c22 = $c2->appendColumn('C22');
        $c3 = new HorizontalContainer('C3');
        $topLevel->append($c3);
        $c31 = $c3->appendColumn('C31');
        $c32 = $c3->appendColumn('C32');
        $c33 = $c3->appendColumn('C33');

        // Now place all items
        $a1  = $aType->create(1);
        $a2  = $aType->create(2);
        $b3  = $bType->create(3);
        $b4  = $bType->create(4);
        $a5  = $aType->create(5);
        $a6  = $aType->create(6);
        $b7  = $bType->create(7);
        $b8  = $bType->create(8);
        $a9  = $aType->create(9);
        $b10 = $bType->create(10);
        $b11 = $bType->create(11);
        $a12 = $aType->create(12);

        $c11->append($a1);
        $c11->append($b4);

        $c21->append($a2);
        $c21->append($a5);

        $c22->append($b3);

        $c31->append($a6);
        $c31->append($a9);

        $c32->append($b7);
        $c32->append($b10);

        $c33->append($b8);
        $c33->append($b11);
        $c33->append($a1);

        $topLevel->append($a12);
        $topLevel->append($b7);

        // Creates the missing item type
        $gridRenderer = new XmlGridRenderer();
        $vboxType = new VerticalContainerType($gridRenderer);
        $hboxType = new HorizontalContainerType($gridRenderer);

        $itemTypeRegistry = new ItemTypeRegistry();
        $itemTypeRegistry->registerType($aType);
        $itemTypeRegistry->registerType($bType);
        $itemTypeRegistry->registerType($vboxType);
        $itemTypeRegistry->registerType($hboxType);

        $renderer = new Renderer($itemTypeRegistry);
        $string = $renderer->render($topLevel);
        $this->assertSame($this->normalizeXML($representation), $this->normalizeXML($string));
    }

    /**
     * Tests the item base class
     */
    public function testBootstrapGridRenderer()
    {
        /**
         *             TOP LEVEL
         * +-----------------------------+
         * | 2 columns, with nested:     |
         * |   C1                        |
         * |   C11           C12         |
         * | +-----------+-------------+ |
         * | |           |  C2         | |
         * | |           |  C21  C22   | |
         * | |           | +----+----+ | |
         * | | A1        | | A2 | A3 | | |
         * | | A4        | | A5 |    | | |
         * | |           | +----+----+ | |
         * | +-----+-----+-------------+ |
         * |  A6                         |
         * |  A7                         |
         * +-----------------------------+
         *
         * C: Container
         * A: Item of type A
         */

        // This the HTML that should be generated:
        $representation = <<<EOT
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6">
            <item id="A1" />
            <item id="A4" />
          </div>
          <div class="col-md-6">
            <div class="container-fluid">
              <div class="row">
                <div class="col-md-6">
                  <item id="A2" />
                  <item id="A5" />
                </div>
                <div class="col-md-6">
                  <item id="A3" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <item id="A6" />
      <item id="A7" />
    </div>
  </div>
</div>
EOT;
        // Create types
        $aType = new ItemAType();

        // Place a top level container and build layout (no items)
        $topLevel = new VerticalContainer('top-level');
        $c1 = new HorizontalContainer('C1');
        $topLevel->append($c1);
        $c11 = $c1->appendColumn('C11');
        $c12 = $c1->appendColumn('C12');
        $c2 = new HorizontalContainer('C2');
        $c12->append($c2);
        $c21 = $c2->appendColumn('C21');
        $c22 = $c2->appendColumn('C22');

        // Now place all items
        $a1  = $aType->create(1);
        $a2  = $aType->create(2);
        $a3  = $aType->create(3);
        $a4  = $aType->create(4);
        $a5  = $aType->create(5);
        $a6  = $aType->create(6);
        $a7  = $aType->create(7);

        $c11->append($a1);
        $c11->append($a4);

        $c21->append($a2);
        $c21->append($a5);

        $c22->append($a3);

        $topLevel->append($a6);
        $topLevel->append($a7);

        // Creates the missing item type
        $gridRenderer = new BootstrapGridRenderer();
        $vboxType = new VerticalContainerType($gridRenderer);
        $hboxType = new HorizontalContainerType($gridRenderer);

        $itemTypeRegistry = new ItemTypeRegistry();
        $itemTypeRegistry->registerType($aType);
        $itemTypeRegistry->registerType($vboxType);
        $itemTypeRegistry->registerType($hboxType);

        $renderer = new Renderer($itemTypeRegistry);
        $string = $renderer->render($topLevel);
        $this->assertSame($this->normalizeXML($representation), $this->normalizeXML($string));
    }
}
