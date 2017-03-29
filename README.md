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
explain the layout we want to display, here is an HTML reprensentation of
what the bootstrap grid we want:

```html
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
```

## Creating a layout

And the associated PHP code for creating the container tree:

```php
// Create types
$aType = new ItemAType();

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
```

## Render the layout

Before initializing the type handlers, we will first create the containers
type handlers: containers are the basis of the grid and are responsible for
the vertical and horizontal layout management.

```php
// This is the class you would change in order to integrate more deeply with
// your own theme and templating engine, or if you do not use bootstrap but
// another grid layout.
$gridRenderer = new BootstrapGridRenderer();
```

Now that we have our top-level container, we need to instanciate the various
type handlers:

```php
$itemTypeRegistry = new ItemTypeRegistry();
$itemTypeRegistry->registerType($aType);
```

Then render it:
```php
$renderer = new Renderer($itemTypeRegistry, $gridRenderer, new ItemIdentifierStrategy());
$string = $renderer->render($topLevel);
```

Which should give you for the ``$string`` the HTML representation we did show
before.

# Why should it render fast?

When you ask for rendering, two very important things are done:

 * the whole container tree is recursively traversed, and all leaf items are
   referenced in a flat index;

 * for each item type, all items are preloaded then rendered in one call.

Because containers do not represent anything related to the database or any
business object either, you don't have anything to preload nor very complex in
their rendering: they are rendered in the end in their inter-dependency order.
