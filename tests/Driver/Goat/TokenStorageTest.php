<?php

namespace MakinaCorpus\Layout\Tests\Driver\Goat;

use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Error\InvalidTokenError;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Tests\Unit\Render\XmlGridRenderer;

/**
 * Test token storage basics
 *
 * WARNING: this test will break your database into pieces.
 */
class TokenStorageTest extends AbstractLayoutTest
{
    protected function assertAllItemsHaveIdentifiers(ContainerInterface $container)
    {
        // Skips the first one that is supposed to have no identifier
        foreach ($container->getAllItems() as $item) {

            $this->assertNotEquals(null, $item->getStorageId());
            $this->assertNotEquals(null, $item->getLayoutId());

            if ($item instanceof ContainerInterface) {
                $this->assertAllItemsHaveIdentifiers($item);
            }
        }
    }

    /**
     * Test storage creation
     */
    public function testCreateAndLoad()
    {
        $context = $this->createPageContext();
        $storage = $this->createStorage();
        $tokenStorage = $this->createTokenStorage();

        $layout1 = $storage->create();
        $layout2 = $storage->create();

        $context->addLayoutList([$layout1->getId(), $layout2->getId()]);
        $token = $context->createEditToken([$layout1->getId()], ['user_id' => 17]);

        // Later on...
        $newToken = $tokenStorage->loadToken($token->getToken());
        $this->assertInstanceOf(EditToken::class, $newToken);
        $this->assertSame($token->getToken(), $newToken->getToken());
        $this->assertTrue($newToken->contains($layout1->getId()));
        $this->assertFalse($newToken->contains($layout2->getId()));
    }

    /**
     * Load multiple load everything with no side effects
     */
    public function testLoadLayoutEmpty()
    {
        $context = $this->createPageContext();
        $tokenStorage = $this->createTokenStorage();
        $token = $context->createEditToken([], ['user_id' => 17]);
        $tokenString = $token->getToken();

        // Seems stupid, but actually we can load nothing
        $tokenStorage->saveToken($token);
        $ret = $tokenStorage->loadMultiple($tokenString, []);
        $this->assertEmpty($ret);

        // But still should failed when token does not exists
        try {
            $tokenStorage->loadMultiple('some_non_existing_arbitrary_token', []);
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Load multiple load everything with no side effects
     */
    public function testLoadLayoutAll()
    {
        $context = $this->createPageContext();
        $storage = $this->createStorage();
        $tokenStorage = $this->createTokenStorage();

        $layout1 = $storage->create();
        $layout2 = $storage->create();
        $layout3 = $storage->create();

        $context->addLayoutList([$layout1->getId(), $layout2->getId(), $layout3->getId()]);
        $token = $context->createEditToken([$layout1->getId(), $layout3->getId()], ['user_id' => 17]);
        $tokenString = $token->getToken();
        $tokenStorage->saveToken($token);

        // Save our editable instances
        $tokenStorage->update($tokenString, $layout1);
        $tokenStorage->update($tokenString, $layout3);

        // Test load single works
        $newLayout1 = $tokenStorage->load($tokenString, $layout1->getId());
        $this->assertSame($layout1->getId(), $newLayout1->getId());

        // Load single will raise exceptions
        try {
            $tokenStorage->load($tokenString, $layout2->getId());
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }

        // Test load multiple works
        $others = $tokenStorage->loadMultiple($token->getToken(), [$layout1->getId(), $layout3->getId()]);
        $this->assertCount(2, $others);

        // Load multiple will raise exceptions
        try {
            $tokenStorage->loadMultiple($tokenString, [$layout2->getId()]);
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }
        try {
            $tokenStorage->loadMultiple($tokenString, [$layout3->getId(), $layout2->getId()]);
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Delete method works (and SQL data is wiped-out)
     */
    public function testRemove()
    {
        $context = $this->createPageContext();
        $storage = $this->createStorage();
        $tokenStorage = $this->createTokenStorage();

        $layout1 = $storage->create();
        $layout2 = $storage->create();
        $layout3 = $storage->create();

        $context->addLayoutList([$layout1->getId(), $layout2->getId(), $layout3->getId()]);
        $token = $context->createEditToken([$layout1->getId(), $layout3->getId()], ['user_id' => 17]);
        $tokenString = $token->getToken();
        $tokenStorage->saveToken($token);

        // Save our editable instances
        $tokenStorage->update($tokenString, $layout1);
        $tokenStorage->update($tokenString, $layout3);

        // And now remove one layout
        $tokenStorage->remove($tokenString, $layout2->getId());
        $this->assertTrue($token->contains($layout1->getId()));
        $this->assertFalse($token->contains($layout2->getId()));
        $this->assertTrue($token->contains($layout3->getId()));

        try {
            $tokenStorage->load($tokenString, $layout2->getId());
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }

        // And the other two still work
        $tokenStorage->load($tokenString, $layout1->getId());
        $tokenStorage->load($tokenString, $layout3->getId());
    }

    /**
     * Delete method works (and SQL data is wiped-out)
     */
    public function testDelete()
    {
        $context = $this->createPageContext();
        $storage = $this->createStorage();
        $tokenStorage = $this->createTokenStorage();

        $layout1 = $storage->create();
        $layout2 = $storage->create();
        $layout3 = $storage->create();

        $context->addLayoutList([$layout1->getId(), $layout2->getId(), $layout3->getId()]);
        $token = $context->createEditToken([$layout1->getId(), $layout3->getId()], ['user_id' => 17]);
        $tokenString = $token->getToken();
        $tokenStorage->saveToken($token);

        // Save our editable instances
        $tokenStorage->update($tokenString, $layout1);
        $tokenStorage->update($tokenString, $layout3);

        // Validate that load still work
        $tokenStorage->loadToken($tokenString);
        $tokenStorage->loadMultiple($tokenString, [$layout1->getId(), $layout3->getId()]);

        // And now delete
        $tokenStorage->deleteAll($tokenString);

        try {
            $tokenStorage->loadToken($tokenString);
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }
        try {
            $tokenStorage->loadMultiple($tokenString, [$layout1->getId()]);
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }
        try {
            $tokenStorage->load($tokenString, $layout3->getId());
            $this->fail();
        } catch (InvalidTokenError $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * We are just reusing MakinaCorpus\Layout\Tests\Unit\RenderTest code
     *
     * @param LayoutInterface $layout
     */
    private function createAwesomelyComplexLayout(LayoutInterface $layout)
    {
        $typeRegistry = $this->createTypeRegistry();
        $aType = $typeRegistry->getType('a');
        $bType = $typeRegistry->getType('b');

        // Place a top level container and build layout (no items)
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
        $c33->append(clone $a1);

        $topLevel->append($a12);
        $topLevel->append(clone $b7);
    }

    /**
     * Get an awesomely complex layout XML representation
     *
     * @param string $topLevelId
     *
     * @return string
     */
    private function getAwesomelyComplexLayoutRepresentation(string $topLevelId)
    {
        return <<<EOT
<vertical id="{$topLevelId}">
    <horizontal id="hbox-C1">
        <column id="vbox-C11">
            <item id="a-1"/>
            <item id="b-4"/>
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
            <item id="a-1" />
        </column>
    </horizontal>
    <item id="a-12" />
    <item id="b-7" />
</vertical>
EOT;
    }

    /**
     * We need to be able to store our layout for further testing
     */
    public function testCreateAndUpdate()
    {
        $tokenStorage = $this->createTokenStorage();
        $storage      = $this->createStorage();
        $typeRegistry = $this->createTypeRegistry();
        $renderer     = $this->createRenderer($typeRegistry, new XmlGridRenderer());

        /** @var \MakinaCorpus\Layout\Storage\DefaultLayout $layout */
        $layout = $storage->create();
        $token = new EditToken('testing', [$layout->getId()]);

        // Save the token, but not yet the layout
        $tokenStorage->saveToken($token);

        // For the sake of simplicity, just create something similar to what
        // the php-layout library does, just see their documentation for more
        // information.
        $this->createAwesomelyComplexLayout($layout);
        $topLevelId = $layout->getId();
        $representation = $this->getAwesomelyComplexLayoutRepresentation($topLevelId);

        // This just tests the testing helpers, and validate that our layout
        // is correct before we do save it.
        $string = $renderer->render($layout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);

        // Now, save it, load it, and ensure rendering is the same.
        $tokenStorage->update('testing', $layout);
        // Ensure that all items have identifiers after save
        $this->assertAllItemsHaveIdentifiers($layout->getTopLevelContainer());

        $otherLayout = $tokenStorage->load('testing', $layout->getId());
        // Ensure that all items have identifiers after load
        $this->assertAllItemsHaveIdentifiers($layout->getTopLevelContainer());

        $string = $renderer->render($otherLayout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);

        // Remove a few elements, compare to a new representation
        $representation = <<<EOT
<vertical id="{$topLevelId}">
    <horizontal id="hbox-C1">
        <column id="vbox-C11">
            <item id="b-4"/>
        </column>
        <column id="vbox-C12">
            <horizontal id="hbox-C2">
                <column id="vbox-C21">
                    <item id="a-2" />
                    <item id="a-5" />
                </column>
            </horizontal>
        </column>
    </horizontal>
    <item id="b-7" />
</vertical>
EOT;
        $otherLayout->getTopLevelContainer()->removeAt(1);
        $otherLayout->getTopLevelContainer()->getAt(0)->getColumnAt(0)->removeAt(0);
        $otherLayout->getTopLevelContainer()->getAt(0)->getColumnAt(1)->getAt(0)->removeColumnAt(1);
        $otherLayout->getTopLevelContainer()->removeAt(1);
        $tokenStorage->update('testing', $otherLayout);

        $thirdLayout = $tokenStorage->load('testing', $layout->getId());
        $string = $renderer->render($thirdLayout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);
    }

    /**
     * Temporary items should not alter persistent ones, persistent storage
     * should correctly save temporary items.
     */
    public function testSaveThenPersist()
    {
        $tokenStorage = $this->createTokenStorage();
        $storage      = $this->createStorage();
        $typeRegistry = $this->createTypeRegistry();
        $renderer     = $this->createRenderer($typeRegistry, new XmlGridRenderer());

        /** @var \MakinaCorpus\Layout\Storage\DefaultLayout $layout */
        $layout = $storage->create();
        $token = new EditToken('testing', [$layout->getId()]);

        // Save the token, but not yet the layout
        $tokenStorage->saveToken($token);

        // For the sake of simplicity, just create something similar to what
        // the php-layout library does, just see their documentation for more
        // information.
        $this->createAwesomelyComplexLayout($layout);
        $topLevelId = $layout->getId();
        $representation = $this->getAwesomelyComplexLayoutRepresentation($topLevelId);

        // This just tests the testing helpers, and validate that our layout
        // is correct before we do save it.
        $string = $renderer->render($layout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);

        // Storing the reloading the temporary layout as persistent should
        // then be the the exact replica of the temporary item
        $storage->update($layout);
        $persistentLayout = $storage->load($layout->getId());
        $string = $renderer->render($persistentLayout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);

        // Now, save it, load it, load it, ensure rendering is the same.
        $tokenStorage->update('testing', $layout);
        $temporaryLayout = $tokenStorage->load('testing', $layout->getId());

        // Storing the reloading the temporary layout as persistent should
        // then be the the exact replica of the temporary item.
        // AND YES THIS IS NECESSARY TO DO IT TWICE: loaded temporary layout
        // may actually be different (different identifiers) than the unsaved
        // one, reason why we do this test a second time.
        $storage->update($temporaryLayout);
        $persistentLayout = $storage->load($layout->getId());
        $string = $renderer->render($persistentLayout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);
    }
}
