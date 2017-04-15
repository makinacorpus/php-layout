<?php

namespace MakinaCorpus\Layout\Grid;

use MakinaCorpus\Layout\Error\GenericError;

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
    private $title = '';
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
    public function getGridIdentifier() : string
    {
        if ($this->storageId) {
            return $this->storageId;
        }

        return $this->type . '-' . $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle() : string
    {
        return $this->title;
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
    public function hasOption(string $name) : bool
    {
        return isset($this->options[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Set options
     *
     * @param string[] $options
     *   Array of options keyed with names
     * @param bool $clear
     *   If set to true, remove all existing options before setting those
     */
    public function setOptions(array $options, bool $clear = false)
    {
        if ($clear) {
            $this->options = [];
        }

        foreach ($options as $name => $value) {
            if (null === $value || '' === $value) {
                unset($this->options[$name]);
            } else {
                if (!is_scalar($value)) {
                    throw new GenericError(sprintf("options '%s' is not a scalar", $name));
                }
                $this->options[$name] = $value;
            }
        }

        $this->toggleUpdateStatus(true);
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
