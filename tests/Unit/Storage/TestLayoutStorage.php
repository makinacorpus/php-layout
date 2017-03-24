<?php

namespace MakinaCorpus\Layout\Tests\Unit\Storage;

use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Storage\LayoutStorageInterface;

/**
 * Very specific version of layout storage, used only for testing purposes
 */
class TestLayoutStorage implements LayoutStorageInterface
{
    private $id = 1;
    private $layouts = [];

    /**
     * {@inheritdoc}
     */
    public function load(int $id) : LayoutInterface
    {
        if (!isset($this->layouts[$id])) {
            throw new GenericError("layout with id %d is not stored", $id);
        }

        return clone $this->layouts[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function exists(int $id) : bool
    {
        return isset($this->layouts[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function listWithConditions(array $conditions) : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function loadMultiple(array $idList) : array
    {
        $ret = [];

        foreach ($idList as $id) {
            if (isset($this->layouts[$id])) {
                $ret[$id] = clone $this->layouts[$id];
            }
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int $id)
    {
        unset($this->layouts[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(LayoutInterface $layout)
    {
        $this->layouts[$layout->getId()] = clone $layout;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $values = []) : LayoutInterface
    {
        $layout = new TestLayout($this->id++);

        $this->layouts[$layout->getId()] = $layout;

        return clone $layout;
    }

    /**
     * {@inheritdoc}
     */
    public function resetCaches()
    {
    }
}
