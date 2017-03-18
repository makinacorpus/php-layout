<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Column container, this only exists to differenciate from the vertical
 * containers and allow to refine rendering.
 *
 * We do not differenciate the type itself, it would not bring any added value
 * since the rendering pipeline is supposed to go through the grid renderer via
 * the GridRendererInterface interface.
 *
 * People might want to differenciate types, they may, but it is not officialy
 * supported by this API and it doesn't guarantee it will work gracefully
 */
class ColumnContainer extends VerticalContainer
{
}
