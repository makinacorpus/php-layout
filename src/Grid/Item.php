<?php

namespace MakinaCorpus\Layout\Grid;

/**
 * Default leaf item implementation
 */
class Item implements ItemInterface
{
    private $id;
    private $isPermanent = false;
    private $layoutId;
    private $options = [];
    private $position = 0;
    private $storageId;
    private $style;
    private $type;
    private $updated = false;

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
    public function isPermanent() : bool
    {
        return $this->isPermanent;
    }

    /**
     * {@inheritdoc}
     */
    public function setStorageId(int $layoutId, int $id, bool $isPermanent = false)
    {
        $this->layoutId = $layoutId;
        $this->storageId = $id;
        $this->isPermanent = $isPermanent;
    }

    /**
     * {@inheritdoc}
     */
    public function setLayoutId(int $layoutId)
    {
        $this->layoutId = $layoutId;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayoutId()
    {
        return $this->layoutId;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorageId()
    {
        return $this->storageId;
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
