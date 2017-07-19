<?php

namespace MakinaCorpus\Layout\Tests\Functionnal;

use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Controller\EditController;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\ColumnContainer;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Tests\Unit\ComparisonTestTrait;
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
        $context      = $this->createContext();
        $tokenStorage = $context->getTokenStorage();
        $typeRegistry = $this->createTypeRegistry();
        $renderer     = new Renderer($typeRegistry, new XmlGridRenderer());
        $controller   = new EditController($typeRegistry, $renderer);
        $aType        = $typeRegistry->getType('a');
        $bType        = $typeRegistry->getType('b');

        // Create the layout and a temporary token, we don't need to keep the
        // token instance because from a functionnal standpoint, we only have
        // the token string on the front side.
        $layout = $context->getLayoutStorage()->create();
        $context->addLayout($layout->getId());
        $context->toggleEditable([$layout->getId()]);
        $editToken = $context->createEditToken([$layout->getId()]);

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
<vertical id="2">
  <horizontal id="3">
    <column id="4"></column>
    <column id="5"></column>
  </horizontal>
</vertical>
EOT
            , $renderer->render($layout->getTopLevelContainer())
        );

        // Now add stuff into the top level container
        $this->assertResponseOk($controller->addAction($context, $editToken, $layout, 0, 'a', 12, 1), <<<EOT
<item id="6"/>
EOT
        );
        $this->assertResponseOk($controller->addAction($context, $editToken, $layout, 0, 'b', 7, 2, 'bar'), <<<EOT
<item id="7" style="bar"/>
EOT
        );

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="2">
  <horizontal id="3">
    <column id="4"></column>
    <column id="5"></column>
  </horizontal>
  <item id="6"/>
  <item id="7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
        );

        /*
         * TEST addColumnContainerAction()
         */

        // And now go for it, every operation on the layout via the controller
        $this->assertResponseOk($controller->addColumnContainerAction($context, $editToken, $layout, $c12->getStorageId(), 0, 2), <<<EOT
<horizontal id="8">
  <column id="9"></column>
  <column id="10"></column>
</horizontal>
EOT
        );

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="2">
  <horizontal id="3">
    <column id="4"></column>
    <column id="5">
      <horizontal id="8">
        <column id="9"></column>
        <column id="10"></column>
      </horizontal>
    </column>
  </horizontal>
  <item id="6"/>
  <item id="7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
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
        $this->assertResponseOk($controller->addAction($context, $editToken, $layout, $c22->getStorageId(), 'a', 5, 0), <<<EOT
<item id="11"/>
EOT
        );

        // Prepend
        $this->assertResponseOk($controller->addAction($context, $editToken, $layout, $c22->getStorageId(), 'b', 12, 0), <<<EOT
<item id="12"/>
EOT
        );

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="2">
  <horizontal id="3">
    <column id="4"></column>
    <column id="5">
      <horizontal id="8">
        <column id="9"></column>
        <column id="10">
          <item id="12"/>
          <item id="11"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <item id="6"/>
  <item id="7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
        );

        /*
         * TEST addColumnAction()
         */

        // Do some magic and insert pretty much the rest
        $controller->addColumnContainerAction($context, $editToken, $layout, $topLevel->getStorageId(), 1, 1);
        $c3 = $topLevel->getAt(1);
        $this->assertTrue($c3 instanceof HorizontalContainer);
        $c3_2 = $c3->getColumnAt(0);
        $this->assertTrue($c3_2 instanceof ColumnContainer);

        $this->assertResponseOk($controller->addColumnAction($context, $editToken, $layout, $c3->getStorageId(), 0), <<<EOT
<column id="15"></column>
EOT
        );
        $c3_1 = $c3->getColumnAt(0);
        $this->assertTrue($c3_1 instanceof ColumnContainer);

        $controller->addColumnAction($context, $editToken, $layout, $c3->getStorageId(), 2);
        $c3_4 = $c3->getColumnAt(2);
        $this->assertTrue($c3_4 instanceof ColumnContainer);

        $controller->addColumnAction($context, $editToken, $layout, $c3->getStorageId(), 2);
        $c3_3 = $c3->getColumnAt(2);
        $this->assertTrue($c3_3 instanceof ColumnContainer);

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="2">
  <horizontal id="3">
    <column id="4"></column>
    <column id="5">
      <horizontal id="8">
        <column id="9"></column>
        <column id="10">
          <item id="12"/>
          <item id="11"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="13">
    <column id="15"></column>
    <column id="14"></column>
    <column id="17"></column>
    <column id="16"></column>
  </horizontal>
  <item id="6"/>
  <item id="7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
        );

        /*
         * TEST add lot of stuff everywhere
         */

        $controller->addAction($context, $editToken, $layout, $c3_1->getStorageId(), 'a', 6);
        // Will be removed
        $controller->addAction($context, $editToken, $layout, $c3_1->getStorageId(), 'b', 12, 1);
        $controller->addAction($context, $editToken, $layout, $c3_1->getStorageId(), 'a', 9, 2);

        // Will be removed
        $controller->addAction($context, $editToken, $layout, $c3_2->getStorageId(), 'b', 52, 2);
        $controller->addAction($context, $editToken, $layout, $c3_2->getStorageId(), 'b', 10, 1);
        $controller->addAction($context, $editToken, $layout, $c3_2->getStorageId(), 'b', 7, 0);

        // Will all be removed
        $controller->addAction($context, $editToken, $layout, $c3_4->getStorageId(), 'a', 54);
        $controller->addAction($context, $editToken, $layout, $c3_4->getStorageId(), 'b', 32);
        $controller->addAction($context, $editToken, $layout, $c3_4->getStorageId(), 'a', 12);

        // None be removed
        $controller->addAction($context, $editToken, $layout, $c3_3->getStorageId(), 'b', 11, 0);
        $controller->addAction($context, $editToken, $layout, $c3_3->getStorageId(), 'b', 8, 0);
        $controller->addAction($context, $editToken, $layout, $c3_3->getStorageId(), 'a', 1, 2, 'foo');

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="2">
  <horizontal id="3">
    <column id="4"></column>
    <column id="5">
      <horizontal id="8">
        <column id="9"></column>
        <column id="10">
          <item id="12"/>
          <item id="11"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="13">
    <column id="15">
      <item id="18"/>
      <item id="12"/>
      <item id="20"/>
    </column>
    <column id="14">
      <item id="23"/>
      <item id="21"/>
      <item id="22"/>
    </column>
    <column id="17">
      <item id="28"/>
      <item id="27"/>
      <item id="29" style="foo"/>
    </column>
    <column id="16">
      <item id="26"/>
      <item id="25"/>
      <item id="24"/>
    </column>
  </horizontal>
  <item id="26"/>
  <item id="7" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
        );

        // FIXME
        return;

        /*
         * TEST removeAction()
         */

        $b12 = $c3_1->getAt(1);
        $this->assertSame('12', $b12->getId());
        $this->assertSame('b', $b12->getType());
        $this->assertResponseOk($controller->removeAction($context, $editToken, $layout, $b12->getStorageId()));

        $b52 = $c3_2->getAt(1);
        $this->assertSame('52', $b52->getId());
        $this->assertSame('b', $b52->getType());
        $this->assertResponseOk($controller->removeAction($context, $editToken, $layout, $b52->getStorageId()));

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="1">
  <horizontal id="2">
    <column id="3"></column>
    <column id="4">
      <horizontal id="7">
        <column id="8"></column>
        <column id="9">
          <item id="11"/>
          <item id="10"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="12">
    <column id="14">
      <item id="17"/>
      <item id="19"/>
    </column>
    <column id="13">
      <item id="22"/>
      <item id="21"/>
    </column>
    <column id="16">
      <item id="27"/>
      <item id="26"/>
      <item id="28" style="foo"/>
    </column>
    <column id="15">
      <item id="25"/>
      <item id="24"/>
      <item id="23"/>
    </column>
  </horizontal>
  <item id="25"/>
  <item id="6" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
        );

        /*
         * TEST removeColumnAction()
         */

        $this->assertResponseOk($controller->removeColumnAction($context, $editToken, $layout, $c3->getStorageId(), 3));

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="1">
  <horizontal id="2">
    <column id="3"></column>
    <column id="4">
      <horizontal id="7">
        <column id="8"></column>
        <column id="9">
          <item id="11"/>
          <item id="10"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <horizontal id="12">
    <column id="14">
      <item id="17"/>
      <item id="19"/>
    </column>
    <column id="13">
      <item id="22"/>
      <item id="21"/>
    </column>
    <column id="16">
      <item id="27"/>
      <item id="26"/>
      <item id="28" style="foo"/>
    </column>
  </horizontal>
  <item id="5"/>
  <item id="6" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
        );

        /*
         * TEST moveAction()
         */

        // Move one item on itself
        $a6 = $c3_1->getAt(0);
        $this->assertSame('6', $a6->getId());
        $this->assertSame('a', $a6->getType());
        $this->assertResponseOk($controller->moveAction($context, $editToken, $layout, $c3_1->getStorageId(), $a6->getStorageId(), 0));

        // Move one item into the same container
        $b8 = $c3_3->getAt(0);
        $this->assertSame('8', $b8->getId());
        $this->assertSame('b', $b8->getType());
        $this->assertResponseOk($controller->moveAction($context, $editToken, $layout, $c3_3->getStorageId(), $b8->getStorageId(), 2));

        // Move one item in another container (warning this item has moved already)
        $a1_foo = $c3_3->getAt(1);
        $this->assertSame('1', $a1_foo->getId());
        $this->assertSame('a', $a1_foo->getType());
        $this->assertSame('foo', $a1_foo->getStyle());
        $this->assertResponseOk($controller->moveAction($context, $editToken, $layout, $c22->getStorageId(), $a1_foo->getStorageId(), 1));

        // Move a vertical container
        $this->assertResponseOk($controller->moveAction($context, $editToken, $layout, $topLevel->getStorageId(), $c3->getStorageId(), 0));

        // Assert our new grid
        $this->assertSameRenderedGrid(
            <<<EOT
<vertical id="1">
  <horizontal id="12">
    <column id="14">
      <item id="17"/>
      <item id="19"/>
    </column>
    <column id="13">
      <item id="22"/>
      <item id="21"/>
    </column>
    <column id="16">
      <item id="26"/>
      <item id="27"/>
    </column>
  </horizontal>
  <horizontal id="2">
    <column id="3"></column>
    <column id="4">
      <horizontal id="7">
        <column id="8"></column>
        <column id="9">
          <item id="11"/>
          <item id="28" style="foo"/>
          <item id="10"/>
        </column>
      </horizontal>
    </column>
  </horizontal>
  <item id="5"/>
  <item id="6" style="bar"/>
</vertical>
EOT
            , $renderer->render($tokenStorage->load($editToken->getToken(), $layout->getId())->getTopLevelContainer())
        );

        // Attempt move into an horizontal (exception)
        try {
            $controller->moveAction($context, $editToken, $layout, $c3->getStorageId(), $a6->getStorageId(), 0);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        // Attempt move a column (exception)
        try {
            $controller->moveAction($context, $editToken, $layout, $c3->getStorageId(), $c3_3->getStorageId(), 1);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        // Attempt move into an item (exception)
        try {
            $controller->moveAction($context, $editToken, $layout, $b8->getStorageId(), $a6->getStorageId(), 0);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
    }
}
