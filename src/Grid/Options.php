<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Any object that carries options
 */
interface Options
{
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
}
