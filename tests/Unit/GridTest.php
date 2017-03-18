<?php

namespace MakinaCorpus\Layout\Tests\Unit;

use MakinaCorpus\Layout\Error\OutOfBoundsError;
use MakinaCorpus\Layout\Grid\HorizontalContainer;
use MakinaCorpus\Layout\Grid\Item;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Grid\VerticalContainer;

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

        $item = new Item('a', 'b', 'some_style');
        $this->assertSame('a', $item->getType());
        $this->assertSame('b', $item->getId());
        $this->assertSame('some_style', $item->getStyle());

        $item->setStyle('another_style');
        $this->assertSame('another_style', $item->getStyle());
    }

    /**
     * Tests the vertical container
     */
    public function testVerticalContainer()
    {
        $container = new VerticalContainer();
        $this->assertTrue($container->isEmpty());
        $this->assertFalse($container->isUpdated());

        $a = new Item('a', 11);
        $b = new VerticalContainer(21);
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
        $container->addAt($b, 1);

        // Updated state
        $container->toggleUpdateStatus(false);
        $this->assertFalse($container->isUpdated());

        // Ensure order
        $items = $container->getAllItems();
        $this->assertCount(7, $container);
        $this->assertCount(7, $items);

        $this->assertSame($a, $items[0]);
        $this->assertSame($b, $items[1]);
        $this->assertSame($c, $items[2]);
        $this->assertSame($d, $items[3]);
        $this->assertSame($e, $items[4]);
        $this->assertSame($f, $items[5]);
        $this->assertSame($g, $items[6]);

        $this->assertSame($a, $container->getAt(0));
        $this->assertSame($b, $container->getAt(1));
        $this->assertSame($c, $container->getAt(2));
        $this->assertSame($d, $container->getAt(3));
        $this->assertSame($e, $container->getAt(4));
        $this->assertSame($f, $container->getAt(5));
        $this->assertSame($g, $container->getAt(6));

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
        $this->assertCount(4, $container);

        $this->assertSame($b, $container->getAt(0));
        $this->assertSame($c, $container->getAt(1));
        $this->assertSame($e, $container->getAt(2));
        $this->assertSame($g, $container->getAt(3));

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
        $this->assertInstanceOf(VerticalContainer::class, $c);
        $a = $container->prependColumn('11');
        $this->assertInstanceOf(VerticalContainer::class, $a);
        $b = $container->createColumnAt(1, '21');
        $this->assertInstanceOf(VerticalContainer::class, $b);
        $d = $container->createColumnAt(127, '41');
        $this->assertInstanceOf(VerticalContainer::class, $d);

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
