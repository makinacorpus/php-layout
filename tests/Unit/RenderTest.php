<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Render\BootstrapGridRenderer;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Tests\Unit\Render\XmlGridRenderer;
use MakinaCorpus\Layout\Render\FlexGridRenderer;

/**
 * Render test, ensures the bottom-top rendering of elements
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    use ComparisonTestTrait;

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
         *
         * Note that duplicated items will have different styles
         */

        // This is pseudo XML reprensentation of what we are waiting for:
        $representation = <<<EOT
<vertical id="vbox-top-level">
    <horizontal id="hbox-C1">
        <column id="vbox-C11">
            <item id="a-1"/>
            <item id="b-4" style="teaser"/>
        </column>
        <column id="vbox-C12">
            <horizontal id="hbox-C2">
                <column id="vbox-C21">
                    <item id="a-2" />
                    <item id="a-5" />
                </column>
                <column id="vbox-C22">
                    <item id="b-3" />
                </column>
            </horizontal>
        </column>
    </horizontal>
    <horizontal id="hbox-C3">
        <column id="vbox-C31">
            <item id="a-6" />
            <item id="a-9" />
        </column>
        <column id="vbox-C32">
            <item id="b-7" />
            <item id="b-10" />
        </column>
        <column id="vbox-C33">
            <item id="b-8" />
            <item id="b-11" />
            <item id="a-1" style="foo"/>
        </column>
    </horizontal>
    <item id="a-12" />
    <item id="b-7" style="bar"/>
</vertical>
EOT;
        // Create types
        $typeRegistry = $this->createTypeRegistry();
        $aType = $typeRegistry->getType('a');
        $bType = $typeRegistry->getType('b');

        // Place a top level container and build layout (no items)
        $topLevel = new TopLevelContainer('top-level');
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
        $a1_1 = $aType->create(1);
        $a1_2 = $aType->create(1, 'foo');
        $a2   = $aType->create(2);
        $b3   = $bType->create(3);
        $b4   = $bType->create(4, 'teaser');
        $a5   = $aType->create(5);
        $a6   = $aType->create(6);
        $b7_1 = $bType->create(7);
        $b7_2 = $bType->create(7, 'bar');
        $b8   = $bType->create(8);
        $a9   = $aType->create(9);
        $b10  = $bType->create(10);
        $b11  = $bType->create(11);
        $a12  = $aType->create(12);

        $c11->append($a1_1);
        $c11->append($b4);

        $c21->append($a2);
        $c21->append($a5);

        $c22->append($b3);

        $c31->append($a6);
        $c31->append($a9);

        $c32->append($b7_1);
        $c32->append($b10);

        $c33->append($b8);
        $c33->append($b11);
        $c33->append($a1_2);

        $topLevel->append($a12);
        $topLevel->append($b7_2);

        $renderer = $this->createRenderer($typeRegistry, new XmlGridRenderer());
        $string = $renderer->render($topLevel);
        $this->assertSameRenderedGrid($representation, $string);
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
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="row">
        <div class="col-md-6">
          <item id="a-1" />
          <item id="a-4" />
        </div>
        <div class="col-md-6">
          <div class="row">
            <div class="col-md-6">
              <item id="a-2" />
              <item id="a-5" />
            </div>
            <div class="col-md-6">
              <item id="a-3" />
            </div>
          </div>
        </div>
      </div>
      <item id="a-6" />
      <item id="a-7" />
    </div>
  </div>
</div>
EOT;
        // Create types
        $typeRegistry = $this->createTypeRegistry();
        $aType = $typeRegistry->getType('a');

        // Place a top level container and build layout (no items)
        $topLevel = new TopLevelContainer('top-level');
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

        $renderer = $this->createRenderer($typeRegistry, new BootstrapGridRenderer());
        $string = $renderer->render($topLevel);
        $this->assertSameRenderedGrid($representation, $string);
    }

    /**
     * Tests the item base class
     */
    public function testFlexGridRenderer()
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
<div>
  <div class="layout-container">
    <div>
      <item id="a-1" />
      <item id="a-4" />
    </div>
    <div>
      <div class="layout-container">
        <div>
          <item id="a-2" />
          <item id="a-5" />
        </div>
        <div>
          <item id="a-3" />
        </div>
      </div>
    </div>
  </div>
  <item id="a-6" />
  <item id="a-7" />
</div>
EOT;
        // Create types
        $typeRegistry = $this->createTypeRegistry();
        $aType = $typeRegistry->getType('a');

        // Place a top level container and build layout (no items)
        $topLevel = new TopLevelContainer('top-level');
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

        $renderer = $this->createRenderer($typeRegistry, new FlexGridRenderer());
        $string = $renderer->render($topLevel);
        $this->assertSameRenderedGrid($representation, $string);
    }

    /**
     * Tests renderAll() and ensures there's no conflict between items
     */
    public function testRenderAllNoConflicts()
    {
        // Creates two very simple layouts with containers that have the same
        // type and identifiers, but have different items inside
        $topLevel1 = new TopLevelContainer('top-level-1');
        $topLevel1->append($vertical1 = new HorizontalContainer('a'));
        $column1 = $vertical1->appendColumn();
        $column1->append(new Item('a', '1'));

        $topLevel2 = new TopLevelContainer('top-level-2');
        $topLevel2->append($vertical2 = new HorizontalContainer('a'));
        $column2 = $vertical2->appendColumn();
        $column2->append(new Item('b', '2'));

        $topLevel3 = new TopLevelContainer('top-level-2');
        $topLevel3->append($vertical3 = new HorizontalContainer('a'));
        $column3 = $vertical3->appendColumn();
        $column3->append(new Item('a', '1', 'foo'));

        $renderer = $this->createRenderer($this->createTypeRegistry(), new XmlGridRenderer());
        $ret = $renderer->renderAll([$topLevel1, $topLevel2, $topLevel3]);

        // @todo unfinished, we MUST implement correctly renderAll()
    }
}
