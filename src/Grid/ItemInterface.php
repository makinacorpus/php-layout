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
     * Has options
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasOption(string $name) : bool;

    /**
     * Get option value
     *
     * @param string $name
     * @param scalar $default
     *   Value to return if not exists, null are considered as non existing
     *
     * @return scalar
     */
    public function getOption(string $name, $default = null);

    /**
     * Set options
     *
     * @param scalar[] $options
     *   Array of options keyed with names
     * @param bool $clear
     *   If set to true, remove all existing options before setting those
     */
    public function setOptions(array $options, bool $clear = false);

    /**
     * Get all options
     *
     * @return array
     */
    public function getOptions() : array;

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
