<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Error\OutOfBoundsError;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\TopLevelContainer;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Grid\ColumnContainer;

/**
 * Basic API-driven composition
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the item base class
     */
    public function testItem()
    {
        $item = new Item('some_type', 'some_id');
        $this->assertSame('some_type', $item->getType());
        $this->assertSame('some_id', $item->getId());
        $this->assertSame(ItemInterface::STYLE_DEFAULT, $item->getStyle());
        $this->assertFalse($item->isPermanent());

        $item = new Item('a', 'b', 'some_style');
        $this->assertSame('a', $item->getType());
        $this->assertSame('b', $item->getId());
        $this->assertSame('some_style', $item->getStyle());

        $item->setStyle('another_style');
        $this->assertSame('another_style', $item->getStyle());

        $item->setStorageId(7, 12, true);
        $this->assertSame(7, $item->getLayoutId());
        $this->assertSame(12, $item->getStorageId());
        $this->assertTrue($item->isPermanent());

        $item->setLayoutId(43);
        $this->assertSame(43, $item->getLayoutId());
    }

    /**
     * Tests item options behavior
     */
    public function testItemOptions()
    {
        $item = new Item('a', 1);
        $this->assertFalse($item->isUpdated());

        $item->setOptions([
            'a' => null,
            'foo' => 'bar',
            'test' => 42,
        ]);

        // Null values are dropped
        $this->assertTrue($item->isUpdated());
        $this->assertFalse($item->hasOption('a'));
        $this->assertTrue($item->hasOption('foo'));
        $this->assertTrue($item->hasOption('test'));
        $this->assertSame('nope', $item->getOption('a', 'nope'));
        $this->assertNull($item->getOption('a'));
        $this->assertSame('bar', $item->getOption('foo', 'nope'));
        $this->assertSame(42, $item->getOption('test', 'nope'));

        // Null values are physically dropped from the array
        $options = $item->getOptions();
        $this->assertCount(2, $options);

        // Setting options don't erase non setted ones
        $item->setOptions([
            'a' => 7,
            'foo' => 'bar bar',
        ]);
        $this->assertTrue($item->hasOption('a'));
        $this->assertTrue($item->hasOption('foo'));
        $this->assertTrue($item->hasOption('test'));
        $this->assertSame(7, $item->getOption('a', 'nope'));
        $this->assertSame('bar bar', $item->getOption('foo', 'nope'));
        $this->assertSame(42, $item->getOption('test', 'nope'));

        // Also test with explicit clear parameter
        $item->setOptions([
            'a' => 13,
            'baz' => 'pouet',
        ], true);
        $this->assertTrue($item->hasOption('a'));
        $this->assertFalse($item->hasOption('foo'));
        $this->assertFalse($item->hasOption('test'));
        $this->assertTrue($item->hasOption('baz'));
        $this->assertSame(13, $item->getOption('a', 'nope'));
        $this->assertSame('pouet', $item->getOption('baz', 'nope'));

        // And ensure errors with non scalar values
        try {
            $item->setOptions(['a' => []]);
            $this->fail();
        } catch (GenericError $e) {
            $this->assertTrue(true);
        }
    }

    /**
     * Ensures that serializing the grid will keep everything ok
     */
    public function testGridSerialization()
    {
        $topLevel = new TopLevelContainer();
        $topLevel->setLayoutId(65);
        $this->assertSame(65, $topLevel->getLayoutId());

        $topLevel->addAt($horizontal = new HorizontalContainer());
        $this->assertSame(65, $horizontal->getLayoutId());

        $horizontal->appendColumn();
        $column1 = $horizontal->getColumnAt(0);
        $this->assertSame(65, $column1->getLayoutId());
        $this->assertSame($horizontal, $column1->getParent());

        $horizontal->appendColumn();
        $column2 = $horizontal->getColumnAt(1);
        $this->assertSame(65, $column2->getLayoutId());
        $this->assertSame($horizontal, $column2->getParent());

        // Now serialize it, and do the same checks
        /** @var \MakinaCorpus\Layout\Grid\TopLevelContainer $newTopLevel */
        $newTopLevel = unserialize(serialize($topLevel));

        $this->assertSame(65, $newTopLevel->getLayoutId());
        $this->assertSame(65, $newTopLevel->getAt(0)->getLayoutId());
        $this->assertSame(65, $newTopLevel->getAt(0)->getColumnAt(0)->getLayoutId());
        $this->assertSame($newTopLevel->getAt(0), $newTopLevel->getAt(0)->getColumnAt(0)->getParent());
        $this->assertSame(65, $newTopLevel->getAt(0)->getColumnAt(1)->getLayoutId());
        $this->assertSame($newTopLevel->getAt(0), $newTopLevel->getAt(0)->getColumnAt(1)->getParent());
    }

    /**
     * Tests the vertical container
     */
    public function testTopLevelContainer()
    {
        $container = new TopLevelContainer();
        $this->assertTrue($container->isEmpty());
        $this->assertFalse($container->isUpdated());

        $a = new Item('a', 11);
        $b = new TopLevelContainer(21);
        $c = new Item('c', 31);
        $d = new Item('d', 41);
        $e = new HorizontalContainer(51);
        $f = new Item('f', 61);
        $g = new Item('g', 71);

        // Normal append
        $container->append($d);
        $this->assertFalse($container->isEmpty());
        $this->assertTrue($container->isUpdated());
        // Prepend a
        $container->prepend($a);
        // Add at with no position = preprend
        $container->addAt($e);
        $this->assertCount(3, $container);
        // Last position
        $container->addAt($f, 3);
        // Out of bound
        $container->addAt($g, 1000);
        // Move the others at the right position
        $container->addAt($c, 1);
        // Move at the same position as the other
        // But wait, you cannot append a top level container
        try {
            $container->addAt($b, 1);
            $this->fail();
        } catch (GenericError $exception) {
            $this->assertTrue(true);
        }

        // Updated state
        $container->toggleUpdateStatus(false);
        $this->assertFalse($container->isUpdated());

        // Ensure order
        $items = $container->getAllItems();
        $this->assertCount(6, $container);
        $this->assertCount(6, $items);

        $this->assertSame($a, $items[0]);
        $this->assertSame($c, $items[1]);
        $this->assertSame($d, $items[2]);
        $this->assertSame($e, $items[3]);
        $this->assertSame($f, $items[4]);
        $this->assertSame($g, $items[5]);

        $this->assertSame($a, $container->getAt(0));
        $this->assertSame($c, $container->getAt(1));
        $this->assertSame($d, $container->getAt(2));
        $this->assertSame($e, $container->getAt(3));
        $this->assertSame($f, $container->getAt(4));
        $this->assertSame($g, $container->getAt(5));

        // Test errors
        try {
            $container->getAt(-2);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }
        try {
            $container->getAt(7);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }

        // Test removal errors
        try {
            $container->removeAt(-2);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }
        try {
            $container->removeAt(7);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }

        // Readonly and error case did not change the updated status
        $this->assertFalse($container->isUpdated());

        // Removal
        $container->removeAt(0);
        $container->removeAt(2);
        $container->removeAt(3);
        $this->assertCount(3, $container);

        $this->assertSame($c, $container->getAt(0));
        $this->assertSame($d, $container->getAt(1));
        $this->assertSame($f, $container->getAt(2));

        // Removal did change the updated status
        $this->assertTrue($container->isUpdated());
    }

    /**
     * Tests the vertical container
     */
    public function testHorizontalContainer()
    {
        $container = new HorizontalContainer();
        $this->assertTrue($container->isEmpty());
        $this->assertFalse($container->isUpdated());
        $this->assertCount(0, $container);

        $c = $container->appendColumn('31');
        $this->assertInstanceOf(ColumnContainer::class, $c);
        $a = $container->prependColumn('11');
        $this->assertInstanceOf(ColumnContainer::class, $a);
        $b = $container->createColumnAt(1, '21');
        $this->assertInstanceOf(ColumnContainer::class, $b);
        $d = $container->createColumnAt(127, '41');
        $this->assertInstanceOf(ColumnContainer::class, $d);

        // Updated state
        $container->toggleUpdateStatus(false);
        $this->assertFalse($container->isUpdated());

        // Ensure order
        $items = $container->getAllItems();
        $this->assertCount(4, $container);
        $this->assertCount(4, $items);

        $this->assertSame($a, $items[0]);
        $this->assertSame($b, $items[1]);
        $this->assertSame($c, $items[2]);
        $this->assertSame($d, $items[3]);

        $this->assertSame($a, $container->getColumnAt(0));
        $this->assertSame($b, $container->getColumnAt(1));
        $this->assertSame($c, $container->getColumnAt(2));
        $this->assertSame($d, $container->getColumnAt(3));

        // Test errors
        try {
            $container->getColumnAt(-2);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }
        try {
            $container->getColumnAt(4);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }
        // Test removal errors
        try {
            $container->removeColumnAt(-2);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }
        try {
            $container->removeColumnAt(4);
            $this->fail();
        } catch (OutOfBoundsError $exception) {
            $this->assertTrue(true);
        }

        // Readonly and error case did not change the updated status
        $this->assertFalse($container->isUpdated());

        // Removal
        $container->removeColumnAt(0);
        $container->removeColumnAt(2);
        $this->assertCount(2, $container);

        $this->assertSame($b, $container->getColumnAt(0));
        $this->assertSame($c, $container->getColumnAt(1));

        // Removal did change the updated status
        $this->assertTrue($container->isUpdated());
    }
}
