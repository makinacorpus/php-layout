<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Leaf item representation
 */
interface ItemInterface
{
    /**
     * Default/no style
     */
    const STYLE_DEFAULT = '_default';

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
