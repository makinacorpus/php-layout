<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Any object that carries options
 */
class NullOptions implements Options
{
    /**
     * {@inheritdoc}
     */
    public function hasOption(string $name) : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(string $name, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options, bool $clear = false)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions() : array
    {
        return [];
    }
}
