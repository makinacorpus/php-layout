<?php

namespace MakinaCorpus\Layout\Storage;

use MakinaCorpus\Layout\Error\GenericError;

/**
 * Grid storage interface
 *
 * One possible SQL storage would be this one, using PostgreSQL schema
 * definition:
 *
 *   CREATE TABLE layout (
 *       id           SERIAL PRIMARY KEY
 *   );
 *
 *   CREATE TABLE layout_data (
 *       id           SERIAL PRIMARY KEY,
 *       parent_id    INTEGER DEFAULT NULL,
 *       layout_id    INTEGER NOT NULL,
 *       item_type    VARCHAR(128) NOT NULL,
 *       item_id      VARCHAR(128) NOT NULL,
 *       style        VARCHAR(128) DEFAULT NULL,
 *       position     INTEGER NOT NULL DEFAULT 0,
 *       options      TEXT DEFAULT NULL,
 *       FOREIGN KEY (parent_id)
 *           REFERENCES layout_data (id)
 *           ON DELETE CASCADE,
 *       FOREIGN KEY (layout_id)
 *           REFERENCES layout (id)
 *           ON DELETE CASCADE
 *   );
 *
 * With such schema, you can update only modified items based on their primary
 * key on layout update, which avoids infamous DELETE/INSERT based grid update.
 *
 * Foreign keys and cascading will also relieve you from a few DELETE statement
 * for delete operations.
 *
 * For reading a full layout tree, and be able to re-create it programmatically
 * without the need of re-sorting items, the following SELECT query will help
 * you:
 *
 *   SELECT d.*
 *       FROM layout_data d
 *   ORDER BY
 *       parent_id ASC NULLS FIRST,
 *       position ASC
 *   WHERE
 *       layout_id = $*
 *   ;
 *
 * If you need to load multiple layout instances in one query, just change
 * the ordering clause this way:
 *
 *   SELECT d.*
 *       FROM layout_data d
 *   ORDER BY
 *       layout_id ASC,
 *       parent_id ASC NULLS FIRST,
 *       position ASC
 *   WHERE
 *       layout_id IN ($*, $* ...)
 *   ;
 *
 * Even with millions of item, all SELECT statements will always only work on
 * the layout_id key, if indexed correctly it will always work gracefully;
 * ordering may happen in filesort or in temporary tables, but no user will
 * never have more than a few dozens of items in a single layout.
 *
 * The only index you will ever need for normal runtime is on the layout_id
 * column, but for a few other rare cases (for exemple, if you delete an item
 * type) you probably will need to add an index on the type column for such
 * maintainance purpose.
 *
 * Please note this only a schema proposal, and you can choose a different
 * path if you wish, or add as many columns as your business logic needds.
 */
interface LayoutStorageInterface
{
    /**
     * Load a layout
     *
     * @param int $id
     *
     * @return LayoutInterface
     *
     * @throws GenericError
     *   If the menu does not exist
     */
    public function load(int $id) : LayoutInterface;

    /**
     * Check that a layout exists
     *
     * @param int $id
     *
     * @return bool
     */
    public function exists(int $id) : bool;

    /**
     * List using conditions
     *
     * @param string[] $conditions
     *   Various conditions, at the discretion of the underlaying implemetation
     *
     * @return int[]
     *   Layout identifiers
     */
    public function listWithConditions(array $conditions) : array;

    /**
     * Load multiple layouts
     *
     * @param int[] $idList
     *
     * @return LayoutInterface[]
     *   Same as load() but an array of it keyed by identifiers
     */
    public function loadMultiple(array $idList) : array;

    /**
     * Delete a layout
     *
     * This will remain silent if the layout does not exist
     *
     * @param int $id
     */
    public function delete(int $id);

    /**
     * Update a layout
     *
     * @param LayoutInterface $layout
     *
     * @throws GenericError
     *   If the menu does not exist
     */
    public function update(LayoutInterface $layout);

    /**
     * Creates and persist an empty layout
     *
     * @param string[] $values
     *   Various values, at the discretion of the underlaying implemetation
     *
     * @return LayoutInterface
     */
    public function create(array $values = []) : LayoutInterface;

    /**
     * Drop all caches if any
     */
    public function resetCaches();
}
