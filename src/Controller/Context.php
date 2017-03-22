<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Storage\LayoutInterface;

/**
 * Represents runtime page context: all layouts arbitrary discovered during page
 * construction will be given to this context. At rendering time, you can use
 * this instance to fetch all layouts.
 *
 * When the user switches to edit mode, you may use this instance to create the
 * edition context.
 */
final class Context
{
    private $layouts = [];
    private $editableIndex = [];
    private $tokenGenerator;

    /**
     * Set token generator
     *
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function setTokenGenerator(TokenGeneratorInterface $tokenGenerator)
    {
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * Get token generator
     *
     * @return TokenGeneratorInterface
     */
    public function getTokenGenerator() : TokenGeneratorInterface
    {
        if (!$this->tokenGenerator) {
            $this->tokenGenerator = new DefaultTokenGenerator();
        }

        return $this->tokenGenerator;
    }

    /**
     * Add layouts to the current context
     *
     * @param LayoutInterface[] $layouts
     * @param bool $editable
     */
    public function add(array $layouts, bool $editable = false)
    {
        foreach ($layouts as $layout) {
            $id = $layout->getId();
            $this->layouts[$id] = $layout;
            $this->editableIndex[$id] = $editable;
        }
    }

    /**
     * Is the given layout editable
     *
     * @param LayoutInterface $layout
     *
     * @return bool
     */
    public function isEditable(LayoutInterface $layout) : bool
    {
        return $this->editableIndex[$layout->getId()] ?? false;
    }

    /**
     * Create edit token
     *
     * @param string[] $additional
     *   Arbitrary information to store and fetch along for security or other
     *   business purpose
     *
     * @return EditToken
     */
    public function createEditToken(array $additional = [])
    {
        return new EditToken($this->getTokenGenerator()->create(), array_keys(array_filter($this->editableIndex)), $additional);
    }
}
