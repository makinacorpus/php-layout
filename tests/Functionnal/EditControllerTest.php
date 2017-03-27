<?php

namespace MakinaCorpus\Layout\Tests\Functionnal;

use MakinaCorpus\Layout\Controller\EditController;
use MakinaCorpus\Layout\Controller\EditToken;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Tests\Unit\ComparisonTestTrait;
use MakinaCorpus\Layout\Tests\Unit\Render\NoIdIdentifierStrategy;
use MakinaCorpus\Layout\Tests\Unit\Render\XmlGridRenderer;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestLayout;
use MakinaCorpus\Layout\Tests\Unit\Storage\TestTokenLayoutStorage;

/**
 * Layout lookup test
 */
class EditControllerTest extends \PHPUnit_Framework_TestCase
{
    use ComparisonTestTrait;

    /**
     * Assert the controller response contents
     *
     * @param array $response
     * @param string $reference
     * @param string $outputKey
     */
    protected function assertResponseOk($response, string $reference = null, $outputKey = 'output')
    {
        $this->assertContains('success', $response);
        $this->assertTrue($response['success']);

        if ($reference) {
            $this->assertContains($outputKey, $response);
            $this->assertSameRenderedGrid($reference, $response[$outputKey], true);
        }
    }

    /**
     * Tests the item base class
     */
    public function testSingleLayoutScenario()
    {
        /**
         * We are going to create this layout:
         *
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
         * Starting from this layout:
         *
         * +-----------------------------+
         * |   C1                        |
         * |   C11           C12         |
         * | +-----------+-------------+ |
         * | |           |             | |
         * | +-----------+-------------+ |
         * |  A12                        |
         * |  *B7                        |
         * +-----------------------------+
         *
         * And passing throught various intermediate states where items
         * are positionned arbitrary and moved all over the place.
         *
         * C: Container
         * A: Item of type A
         * B: Item of type B
         * *: Duplicated item
         *
         * Note that duplicated items will have different styles
         */

        // Create the environment
        $tokenStorage = new TestTokenLayoutStorage();
        $typeRegistry = $this->createTypeRegistry();
        $renderer     = new Renderer($typeRegistry, new XmlGridRenderer(), new NoIdIdentifierStrategy());
        $controller   = new EditController($tokenStorage, $typeRegistry, $renderer);
        $aType        = $typeRegistry->getType('a');
        $bType        = $typeRegistry->getType('b');

        // Create the layout and a temporary token, we don't need to keep the
        // token instance because from a functionnal standpoint, we only have
        // the token string on the front side.
        $tokenStorage->saveToken(new EditToken('testing', [7]));
        $layout = new TestLayout(7);

        // Create the grid and keep object references, becaue we need their
        // storage identifiers to work with
        $topLevel = $layout->getTopLevelContainer();
        $c1 = new HorizontalContainer('C1');
        $topLevel->append($c1);
        $c11 = $c1->appendColumn('C11');
        $c12 = $c1->appendColumn('C12');

        // We need to save the layout to have storage identifiers
        $tokenStorage->update('testing', $layout);

        // Just for fun, this out starting grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox"></column>
  </horizontal>
</vertical>
EOT
            , $renderer->render($layout->getTopLevelContainer())
        );

        // Now add stuff into the top level container
        $this->assertResponseOk($controller->addAction('testing', 7, 0, 'a', 12, 1), <<<EOT
<item id="leaf:a/12"/>
EOT
        );
        $this->assertResponseOk($controller->addAction('testing', 7, 0, 'b', 7, 2, 'bar'), <<<EOT
<item id="leaf:b/7" style="bar"/>
EOT
        );

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox"></column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        /*
         * TEST addColumnContainerAction()
         */

        // And now go for it, every operation on the layout via the controller
        $this->assertResponseOk($controller->addColumnContainerAction('testing', 7, $c12->getStorageId(), 0, 2), <<<EOT
<horizontal id="container:hbox">
  <column id="container:vbox"></column>
  <column id="container:vbox"></column>
</horizontal>
EOT
        );

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox">
      <horizontal id="container:hbox">
        <column id="container:vbox"></column>
        <column id="container:vbox"></column>
      </horizontal>
    </column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        /*
         * TEST addItemAction()
         */

        // First find the added container
        /** @var \MakinaCorpus\Layout\Grid\HorizontalContainer $c2 */
        $c2 = $c12->getAt(0);
        $c21 = $c2->getColumnAt(0);
        $c22 = $c2->getColumnAt(1);

        // Append
        $this->assertResponseOk($controller->addAction('testing', 7, $c22->getStorageId(), 'a', 5, 0), <<<EOT
<item id="leaf:a/5"/>
EOT
        );

        // Prepend
        $this->assertResponseOk($controller->addAction('testing', 7, $c22->getStorageId(), 'b', 12, 0), <<<EOT
<item id="leaf:b/12"/>
EOT
        );

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox">
      <horizontal id="container:hbox">
        <column id="container:vbox"></column>
        <column id="container:vbox">
          <item id="leaf:b/12"/>
          <item id="leaf:a/5"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        /*
         * TEST addColumnAction()
         */

        // Do some magic and insert pretty much the rest
        $controller->addColumnContainerAction('testing', 7, $topLevel->getStorageId(), 1, 1);
        $c3 = $topLevel->getAt(1);
        $this->assertTrue($c3 instanceof HorizontalContainer);
        $c3_2 = $c3->getColumnAt(0);
        $this->assertTrue($c3_2 instanceof ColumnContainer);

        $this->assertResponseOk($controller->addColumnAction('testing', 7, $c3->getStorageId(), 0), <<<EOT
<column id="container:vbox"></column>
EOT
        );
        $c3_1 = $c3->getColumnAt(0);
        $this->assertTrue($c3_1 instanceof ColumnContainer);

        $controller->addColumnAction('testing', 7, $c3->getStorageId(), 2);
        $c3_4 = $c3->getColumnAt(2);
        $this->assertTrue($c3_4 instanceof ColumnContainer);

        $controller->addColumnAction('testing', 7, $c3->getStorageId(), 2);
        $c3_3 = $c3->getColumnAt(2);
        $this->assertTrue($c3_3 instanceof ColumnContainer);

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox">
      <horizontal id="container:hbox">
        <column id="container:vbox"></column>
        <column id="container:vbox">
          <item id="leaf:b/12"/>
          <item id="leaf:a/5"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox"></column>
    <column id="container:vbox"></column>
    <column id="container:vbox"></column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        /*
         * TEST add lot of stuff everywhere
         */

        $controller->addAction('testing', 7, $c3_1->getStorageId(), 'a', 6);
        // Will be removed
        $controller->addAction('testing', 7, $c3_1->getStorageId(), 'b', 12, 1);
        $controller->addAction('testing', 7, $c3_1->getStorageId(), 'a', 9, 2);

        // Will be removed
        $controller->addAction('testing', 7, $c3_2->getStorageId(), 'b', 52, 2);
        $controller->addAction('testing', 7, $c3_2->getStorageId(), 'b', 10, 1);
        $controller->addAction('testing', 7, $c3_2->getStorageId(), 'b', 7, 0);

        // Will all be removed
        $controller->addAction('testing', 7, $c3_4->getStorageId(), 'a', 54);
        $controller->addAction('testing', 7, $c3_4->getStorageId(), 'b', 32);
        $controller->addAction('testing', 7, $c3_4->getStorageId(), 'a', 12);

        // None be removed
        $controller->addAction('testing', 7, $c3_3->getStorageId(), 'b', 11, 0);
        $controller->addAction('testing', 7, $c3_3->getStorageId(), 'b', 8, 0);
        $controller->addAction('testing', 7, $c3_3->getStorageId(), 'a', 1, 2, 'foo');

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox">
      <horizontal id="container:hbox">
        <column id="container:vbox"></column>
        <column id="container:vbox">
          <item id="leaf:b/12"/>
          <item id="leaf:a/5"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="container:hbox">
    <column id="container:vbox">
      <item id="leaf:a/6"/>
      <item id="leaf:b/12"/>
      <item id="leaf:a/9"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/7"/>
      <item id="leaf:b/52"/>
      <item id="leaf:b/10"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/8"/>
      <item id="leaf:b/11"/>
      <item id="leaf:a/1" style="foo"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:a/12"/>
      <item id="leaf:b/32"/>
      <item id="leaf:a/54"/>
    </column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        /*
         * TEST removeAction()
         */

        $b12 = $c3_1->getAt(1);
        $this->assertSame('12', $b12->getId());
        $this->assertSame('b', $b12->getType());
        $this->assertResponseOk($controller->removeAction('testing', 7, $b12->getStorageId()));

        $b52 = $c3_2->getAt(1);
        $this->assertSame('52', $b52->getId());
        $this->assertSame('b', $b52->getType());
        $this->assertResponseOk($controller->removeAction('testing', 7, $b52->getStorageId()));

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox">
      <horizontal id="container:hbox">
        <column id="container:vbox"></column>
        <column id="container:vbox">
          <item id="leaf:b/12"/>
          <item id="leaf:a/5"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="container:hbox">
    <column id="container:vbox">
      <item id="leaf:a/6"/>
      <item id="leaf:a/9"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/7"/>
      <item id="leaf:b/10"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/8"/>
      <item id="leaf:b/11"/>
      <item id="leaf:a/1" style="foo"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:a/12"/>
      <item id="leaf:b/32"/>
      <item id="leaf:a/54"/>
    </column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        /*
         * TEST removeColumnAction()
         */

        $this->assertResponseOk($controller->removeColumnAction('testing', 7, $c3->getStorageId(), 3));

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox">
      <horizontal id="container:hbox">
        <column id="container:vbox"></column>
        <column id="container:vbox">
          <item id="leaf:b/12"/>
          <item id="leaf:a/5"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="container:hbox">
    <column id="container:vbox">
      <item id="leaf:a/6"/>
      <item id="leaf:a/9"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/7"/>
      <item id="leaf:b/10"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/8"/>
      <item id="leaf:b/11"/>
      <item id="leaf:a/1" style="foo"/>
    </column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        /*
         * TEST moveAction()
         */

        // Move one item on itself
        $a6 = $c3_1->getAt(0);
        $this->assertSame('6', $a6->getId());
        $this->assertSame('a', $a6->getType());
        $this->assertResponseOk($controller->moveAction('testing', 7, $c3_1->getStorageId(), $a6->getStorageId(), 0));

        // Move one item into the same container
        $b8 = $c3_3->getAt(0);
        $this->assertSame('8', $b8->getId());
        $this->assertSame('b', $b8->getType());
        $this->assertResponseOk($controller->moveAction('testing', 7, $c3_3->getStorageId(), $b8->getStorageId(), 2));

        // Move one item in another container (warning this item has moved already)
        $a1_foo = $c3_3->getAt(1);
        $this->assertSame('1', $a1_foo->getId());
        $this->assertSame('a', $a1_foo->getType());
        $this->assertSame('foo', $a1_foo->getStyle());
        $this->assertResponseOk($controller->moveAction('testing', 7, $c22->getStorageId(), $a1_foo->getStorageId(), 1));

        // Move a vertical container
        $this->assertResponseOk($controller->moveAction('testing', 7, $topLevel->getStorageId(), $c3->getStorageId(), 0));

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="container:vbox">
  <horizontal id="container:hbox">
    <column id="container:vbox">
      <item id="leaf:a/6"/>
      <item id="leaf:a/9"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/7"/>
      <item id="leaf:b/10"/>
    </column>
    <column id="container:vbox">
      <item id="leaf:b/11"/>
      <item id="leaf:b/8"/>
    </column>
  </horizontal>
  <horizontal id="container:hbox">
    <column id="container:vbox"></column>
    <column id="container:vbox">
      <horizontal id="container:hbox">
        <column id="container:vbox"></column>
        <column id="container:vbox">
          <item id="leaf:b/12"/>
          <item id="leaf:a/1" style="foo"/>
          <item id="leaf:a/5"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <item id="leaf:a/12"/>
  <item id="leaf:b/7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load('testing', 7)->getTopLevelContainer())
        );

        // Attempt move into an horizontal (exception)
        try {
            $controller->moveAction('testing', 7, $c3->getStorageId(), $a6->getStorageId(), 0);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        // Attempt move a column (exception)
        try {
            $controller->moveAction('testing', 7, $c3->getStorageId(), $c3_3->getStorageId(), 1);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        // Attempt move into an item (exception)
        try {
            $controller->moveAction('testing', 7, $b8->getStorageId(), $a6->getStorageId(), 0);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
    }
}
