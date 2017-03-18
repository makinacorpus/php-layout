# PHP basic layout API

Object representation of a display layout, based upon CSS basic functionnality:

 * all items are always stacked vertically in containers;
 * a container can be a column set, case in which each column contains itself
   another container which will stack items vertically.

Once you understood this basic principle, you can use the API.

Leaf items (non container) are represented by an interface, with very few
methods to implement, and a handler service, whose basic features are:

 * preload all items at once;
 * render a set of items at once.

You probably understood it now, this was tailored for rendering speed on more
complex frameworks that are already able to provide support for preloading
and pre-rendering objects (for example, Drupal).

> Please note this is, as of today, rather a playground than an API you could
> use, but it worth the short of trying.

# Before going deeper

You should be aware the goal of this API is to, in a near future, be integrated
into page composition tools, and its API to be hidden behind user friendly UI
for contributing. Don't be afraid of the concrete code example below, it only
shows how it works, but won't be revelant for concrete use cases of this API.

# Complete example

For the sake of simplicity, we are going to use the unit test example to
explain the layout we want to display, here is an XML reprensentation of
it:

```xml
<container id="top-level">
    <horizontal id="C1">
        <container id="C11">
            <item id="A1" />
            <item id="B4" />
        </container>
        <container id="C12">
            <horizontal id="C2">
                <container id="C21">
                    <item id="A2" />
                    <item id="A5" />
                </container>
                <container id="C22">
                    <item id="B3" />
                </container>
            </horizontal>
        </container>
    </horizontal>
    <horizontal id="C3">
        <container id="C31">
            <item id="A6" />
            <item id="A9" />
        </container>
        <container id="C32">
            <item id="B7" />
            <item id="B10" />
        </container>
        <container id="C33">
            <item id="B8" />
            <item id="B11" />
            <item id="A1" />
        </container>
    </horizontal>
    <item id="A12" />
    <item id="B7" />
</container>
```

Which, for the sake of comprehensibility, would display as such (yes I am sorry
this is basic copy/paste of the comment lying in the unit test):

```
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
```

## Creating a layout

And the associated PHP code for creating the container tree:

```php
// Create types
$aType = new ItemAType();
$bType = new ItemBType();

// Place a top level container and build layout (no items)
$topLevel = new ArbitraryContainer('top-level');
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
```

## Render the layout

Before initializint the type handlers, we will first create the containers
type handlers: containers are the basis of the grid and are responsible for
the vertical and horizontal layout management.

```php
// This is the class you would override to integrate with your own theme
// and templating engine.
$gridRenderer = new XmlGridRenderer();
$vboxType = new ArbitraryContainerType($gridRenderer);
$hboxType = new HorizontalContainerType($gridRenderer);
```

Now that we have our top-level container, we need to instanciate the various
type handlers:

```php
$itemTypeRegistry = new ItemTypeRegistry();
$itemTypeRegistry->registerType($aType);
$itemTypeRegistry->registerType($bType);
$itemTypeRegistry->registerType($vboxType);
$itemTypeRegistry->registerType($hboxType);
```

Then render it:
```php
$renderer = new Renderer($itemTypeRegistry);
$string = $renderer->render($topLevel);
```

Which should give you for the ``$string`` the XML representation we did show
before.

# Why is it very fast to render?

When you ask for rendering, two very important things are done:

 * the whole container tree is recursively traversed, and all leaf items are
   referenced in a flat index;

 * for each item type, all items are preloaded then rendered in one call.

Because containers do not represent anything related to the database or any
business object either, you don't have anything to preload nor very complex in
their rendering: they are rendered in the end in their inter-dependency order.
