<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Controller\EditToken;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Tests\Unit\Render\XmlGridRenderer;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestLayout;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestTokenLayoutStorage;

/**
 * Layout lookup test
 */
class LayoutLookupTest extends \PHPUnit_Framework_TestCase
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

        // We will need a storage to have identifiers
        $storage = new TestTokenLayoutStorage();
        $storage->saveToken(new EditToken('testing', []));

        // Create types
        $typeRegistry = $this->createTypeRegistry(new XmlGridRenderer());
        $aType = $typeRegistry->getType('a');
        $bType = $typeRegistry->getType('b');

        // Place a top level container and build layout (no items)
        $layout = new TestLayout(7);
        $topLevel = $layout->getTopLevelContainer();
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

        // We need to save the layout to have storage identifiers
        $storage->update('testing', $layout);

        // And go for some searches.
        $found = $layout->findContainer($c21->getStorageId());
        $this->assertSame($c21, $found);
        $found = $layout->findContainerOf($b10->getStorageId());
        $this->assertSame($c32, $found);
        $found = $layout->findItem($b3->getStorageId());
        $this->assertSame($b3, $found);

        // And also invalid searches
        try {
            // Container is an item
            $layout->findContainer($a5->getStorageId());
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
        try {
            // Container does not exists
            $layout->findContainer(555);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
        try {
            // Item does not exists
            $layout->findItem(666);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
        try {
            // Item or container does not exists
            $layout->findContainerOf(999);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
    }
}
