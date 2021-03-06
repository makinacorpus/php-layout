<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Leaf item representation
 */
interface ItemInterface extends Options
{
    /**
     * Default/no style
     */
    const STYLE_DEFAULT = '_default';

    /**
     * Does this item already have been stored
     *
     * @return bool
     */
    public function isPermanent() : bool;

    /**
     * For storage engine only
     *
     * @param int $layoutId
     * @param int $id
     */
    public function setStorageId(int $layoutId, int $id);

    /**
     * For storage engine only
     *
     * @param int $layoutId
     */
    public function setLayoutId(int $layoutId);

    /**
     * For storage engine only
     *
     * @return int|null
     */
    public function getLayoutId();

    /**
     * For storage engine only
     *
     * @return int|null
     */
    public function getStorageId();

    /**
     * Set human readable title
     *
     * Title has no business purpose, users might build an edit UI using it.
     *
     * @param string $title
     */
    public function setTitle(string $title);

    /**
     * Get item grid identifier
     *
     * @return string
     */
    public function getGridIdentifier() : string;

    /**
     * Get human readable title
     *
     * Title has no business purpose, users might build an edit UI using it.
     *
     * @return string
     */
    public function getTitle() : string;

    /**
     * Get node identifier
     *
     * @return int|string
     */
    public function getId() : string;

    /**
     * Get item type
     *
     * @return string
     */
    public function getType() : string;

    /**
     * Set display style
     *
     * @param string $style
     *
     * @return $this
     */
    public function setStyle(string $style) : Item;

    /**
     * Get display style
     *
     * @return string
     */
    public function getStyle() : string;

    /**
     * Is this instance updated
     *
     * @return bool
     */
    public function isUpdated() : bool;

    /**
     * Set the updated status of this instance
     *
     * @param bool $toggle
     */
    public function toggleUpdateStatus(bool $toggle);
}
