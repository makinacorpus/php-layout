<?php

namespace MakinaCorpus\Layout\Tests\Driver\Goat;

use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Storage\DefaultLayout;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Tests\Unit\Render\XmlGridRenderer;

/**
 * Test storage basics
 *
 * WARNING: this test will break your database into pieces.
 */
class StorageTest extends AbstractLayoutTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Test storage creation
     */
    public function testCreateAndLoad()
    {
        $storage = $this->createStorage();

        $layout = $storage->create();
        $this->assertInstanceOf(DefaultLayout::class, $layout);
        $this->assertNotEmpty($layout->getId());

        // We must always have a top level container
        $container = $layout->getTopLevelContainer();
        $this->assertInstanceOf(TopLevelContainer::class, $container);
        $this->assertSame($layout->getId(), (int)$container->getId());

        // Load it
        $otherLayout = $storage->load($layout->getId());
        $this->assertNotSame($layout, $otherLayout);
        $this->assertSame($layout->getId(), $otherLayout->getId());
        $otherContainer = $otherLayout->getTopLevelContainer();
        $this->assertInstanceOf(TopLevelContainer::class, $otherContainer);
        $this->assertSame($otherLayout->getId(), (int)$otherContainer->getId());
    }

    /**
     * Attempt a non existing layout load
     */
    public function testLoadThrowsExceptions()
    {
        try {
            // -1 is not an invalid value, but I'm sure no layouts will ever
            // have this identifier
            $this->createStorage()->load(-1);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Load multiple load everything with no side effects
     */
    public function testLoadMultipleAllOK()
    {
        $storage = $this->createStorage();

        $layout1 = $storage->create();
        $layout2 = $storage->create();
        $layout3 = $storage->create();

        $ret = $storage->loadMultiple([]);
        $this->assertCount(0, $ret);

        $ret = $storage->loadMultiple([
            $layout1->getId(),
            $layout3->getId(),
        ]);

        $this->assertCount(2, $ret);
        $this->assertSame($layout1->getId(), $ret[$layout1->getId()]->getId());
        $this->assertSame($layout3->getId(), $ret[$layout3->getId()]->getId());

        $this->createAwesomelyComplexLayout($layout2);
        $this->createAwesomelyComplexLayout($layout3);
        $storage->update($layout2);
        $storage->update($layout3);

        $ret = $storage->loadMultiple([
            $layout2->getId(),
            $layout3->getId(),
        ]);

        $this->assertFalse($ret[$layout2->getId()]->getTopLevelContainer()->isEmpty());
        $this->assertFalse($ret[$layout3->getId()]->getTopLevelContainer()->isEmpty());
    }

    /**
     * Load multiple downgrades when there are missing layouts
     */
    public function testLoadMultipleAllSomeDontExist()
    {
        $storage = $this->createStorage();

        $layout1 = $storage->create();

        $ret = $storage->loadMultiple([
            $layout1->getId(),
            $layout1->getId() + 1,
        ]);

        $this->assertCount(1, $ret);
        $this->assertSame($layout1->getId(), $ret[$layout1->getId()]->getId());
    }

    /**
     * Tests load with conditions
     */
    public function testLoadWithCondition()
    {
        $this->markTestSkipped();

        // This test will break you data
        $this
            ->getDatabaseConnection()
            ->query("delete from {layout} where site_id = 666 or site_id = 999 or node_id is null")
        ;

        $storage = $this->createStorage();

        // Basic use cases, unknown column or no condition
        try {
            $storage->listWithConditions([]);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
        try {
            $storage->listWithConditions(['foo' => 1]);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }

        // We need to create nodes first
        $node1 = $this->node[] = new \stdClass();
        $node1->type = 'page';
        node_save($node1);
        $node2 = $this->node[] = new \stdClass();
        $node2->type = 'page';
        node_save($node2);

        $layout1 = $storage->create([
            'node_id' => $node1->nid,
        ]);
        $layout2 = $storage->create([
            'node_id' => $node2->nid,
            'site_id' => 999,
        ]);
        $layout3 = $storage->create([
            'site_id' => 666,
        ]);
        $layout4 = $storage->create();
        $layout5 = $storage->create();
        $layout6 = $storage->create([
            'site_id' => 666,
            'region'  => 'foo',
        ]);
        $layout7 = $storage->create([
            'node_id' => $node1->nid,
            'site_id' => 999,
        ]);

        $idList = $storage->listWithConditions(['node_id' => 137]);
        $this->assertCount(0, $idList);

        $idList = $storage->listWithConditions(['site_id' => 666]);
        $this->assertCount(2, $idList);
        $this->assertContains($layout3->getId(), $idList);
        $this->assertContains($layout6->getId(), $idList);

        $idList = $storage->listWithConditions(['node_id' => $node1->nid]);
        $this->assertCount(2, $idList);
        $this->assertContains($layout1->getId(), $idList);
        $this->assertContains($layout7->getId(), $idList);

        $idList = $storage->listWithConditions(['node_id' => $node1->nid, 'site_id' => null]);
        $this->assertCount(1, $idList);
        $this->assertContains($layout1->getId(), $idList);

        $idList = $storage->listWithConditions(['node_id' => null, 'site_id' => null]);
        $this->assertCount(2, $idList);
        $this->assertContains($layout4->getId(), $idList);
        $this->assertContains($layout5->getId(), $idList);

        $idList = $storage->listWithConditions(['node_id' => $node2->nid, 'site_id' => 999]);
        $this->assertCount(1, $idList);
        $this->assertContains($layout2->getId(), $idList);

        $idList = $storage->listWithConditions(['region' => 'foo']);
        $this->assertCount(1, $idList);
        $this->assertContains($layout6->getId(), $idList);
    }

    /**
     * Exists method works
     */
    public function testExists()
    {
        $storage = $this->createStorage();
        $this->assertFalse($storage->exists(-1));

        $layout1 = $storage->create();
        $this->assertTrue($storage->exists($layout1->getId()));
    }

    /**
     * Delete method works (and SQL data is wiped-out)
     */
    public function testDelete()
    {
        $storage = $this->createStorage();

        $layout1 = $storage->create();
        $this->createAwesomelyComplexLayout($layout1);
        $storage->update($layout1);

        $storage->delete($layout1->getId());

        return;//@todo
        $database = $this->getDatabaseConnection();
        $countLayout = (bool)$database->query("select 1 from {layout} where id = ?", [$layout1->getId()])->fetchField();
        $this->assertFalse($countLayout);
        $countItems = (bool)$database->query("select 1 from {layout_data} where layout_id = ?", [$layout1->getId()])->fetchField();
        $this->assertFalse($countItems);
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

        // Add a few options, for fun
        $b8->setOptions(['foo' => 'bar']);
        $c31->setOptions(['a' => 12, 'b' => 'test']);

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
     * We need to be able to store our layout for further testing
     */
    public function testCreateAndUpdate()
    {
        $storage = $this->createStorage();
        $typeRegistry = $this->createTypeRegistry();
        $renderer = $this->createRenderer($typeRegistry, new XmlGridRenderer());

        /** @var \MakinaCorpus\Layout\Storage\DefaultLayout $layout */
        $layout = $storage->create();

        // For the sake of simplicity, just create something similar to what
        // the php-layout library does, just see their documentation for more
        // information.
        $this->createAwesomelyComplexLayout($layout);

        $topLevelId = $layout->getId();
        $representation = <<<EOT
<vertical id="{$topLevelId}">
    <horizontal id="C1">
        <column id="C11">
            <item id="a-1"/>
            <item id="b-4"/>
        </column>
        <column id="C12">
            <horizontal id="C2">
                <column id="C21">
                    <item id="a-2" />
                    <item id="a-5" />
                </column>
                <column id="C22">
                    <item id="b-3" />
                </column>
            </horizontal>
        </column>
    </horizontal>
    <horizontal id="C3">
        <column id="C31">
            <item id="a-6" />
            <item id="a-9" />
        </column>
        <column id="C32">
            <item id="b-7" />
            <item id="b-10" />
        </column>
        <column id="C33">
            <item id="b-8" />
            <item id="b-11" />
            <item id="a-1" />
        </column>
    </horizontal>
    <item id="a-12" />
    <item id="b-7" />
</vertical>
EOT;

        // This just tests the testing helpers, and validate that our layout
        // is correct before we do save it.
        $string = $renderer->render($layout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);

        // Now, save it, load it, and ensure rendering is the same.
        $storage->update($layout);

        $otherLayout = $storage->load($layout->getId());
        $string = $renderer->render($otherLayout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);

        // Check options were loaded correctly
        $b8 = $otherLayout->getTopLevelContainer()->getAt(1)->getColumnAt(2)->getAt(0);
        $this->assertTrue($b8->hasOption('foo'));
        $this->assertFalse($b8->hasOption('a'));
        $this->assertSame('bar', $b8->getOption('foo', 'nope'));
        $c31 = $otherLayout->getTopLevelContainer()->getAt(1)->getColumnAt(0);
        $this->assertTrue($c31->hasOption('a'));
        $this->assertTrue($c31->hasOption('b'));
        $this->assertSame(12, $c31->getOption('a', 'nope'));
        $this->assertSame('test', $c31->getOption('b', 'nope'));

        // Remove a few elements, compare to a new representation
        $representation = <<<EOT
<vertical id="{$topLevelId}">
    <horizontal id="C1">
        <column id="C11">
            <item id="b-4"/>
        </column>
        <column id="C12">
            <horizontal id="C2">
                <column id="C21">
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
        $storage->update($otherLayout);

        $thirdLayout = $storage->load($layout->getId());
        $string = $renderer->render($thirdLayout->getTopLevelContainer());
        // @todo $this->assertSameRenderedGrid($representation, $string);

        // Adds new elements, compare to a new representation

        // Changes a few item styles, and ensure update
    }
}
