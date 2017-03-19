<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Default leaf item implementation
 */
class Item implements ItemInterface
{
    private $type;
    private $id;
    private $style;
    private $position = 0;
    private $updated = false;
    private $options = [];

    /**
     * Default constructor
     *
     * @param string $id
     * @param string $type
     * @param string $style
     */
    public function __construct(string $type, string $id, string $style = ItemInterface::STYLE_DEFAULT)
    {
        $this->type = $type;
        $this->id = $id;
        $this->style = $style;
    }

    /**
     * {@inheritdoc}
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setStyle(string $style) : Item
    {
        $this->style = $style;
        $this->toggleUpdateStatus(true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStyle() : string
    {
        return $this->style;
    }

    /**
     * {@inheritdoc}
     */
    public function isUpdated() : bool
    {
        return $this->updated;
    }

    /**
     * {@inheritdoc}
     */
    public function toggleUpdateStatus(bool $toggle)
    {
        $this->updated = $toggle;
    }
}
