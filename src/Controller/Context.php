<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Storage\LayoutStorageInterface;
use MakinaCorpus\Layout\Storage\TokenLayoutStorageInterface;

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
    private $currentToken;
    private $editableIndex = [];
    private $layouts = [];
    private $storage;
    private $tokenGenerator;
    private $tokenStorage;

    /**
     * Default constructor
     *
     * @param LayoutStorageInterface $storage
     * @param TokenLayoutStorageInterface $tokenStorage
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(LayoutStorageInterface $storage, TokenLayoutStorageInterface $tokenStorage, TokenGeneratorInterface $tokenGenerator)
    {
        $this->storage = $storage;
        $this->tokenStorage = $tokenStorage;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * Get token generator
     *
     * @return TokenGeneratorInterface
     */
    public function getTokenGenerator() : TokenGeneratorInterface
    {
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
     * Are they contextual layouts
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->layouts);
    }

    /**
     * Get all layouts
     *
     * @return LayoutInterface[]
     */
    public function getAll() : array
    {
        return $this->layouts;
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
     * Is the current page pending edit mode
     *
     * @return bool
     */
    public function hasToken() : bool
    {
        return null !== $this->currentToken;
    }

    /**
     * Reset current token
     */
    public function resetToken()
    {
        $this->currentToken = null;
    }

    /**
     * Set current context edit token
     *
     * @param EditToken $token
     */
    public function setCurrentToken(EditToken $token)
    {
        if ($this->currentToken) {
            throw new GenericError("you cannot create a new token, context is already in edit mode");
        }

        $this->currentToken = $token;
    }

    /**
     * Get current token string
     *
     * @return string
     */
    public function getCurrentToken() : EditToken
    {
        if (!$this->currentToken) {
            throw new GenericError("there is no token set yet");
        }

        return $this->currentToken;
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
        if ($this->currentToken) {
            throw new GenericError("you cannot create a new token, context is already in edit mode");
        }

        $token = new EditToken($this->getTokenGenerator()->create(), array_keys(array_filter($this->editableIndex)), $additional);
        $this->tokenStorage->saveToken($token);

        return $this->currentToken = $token;
    }

    /**
     * Commit session changes and restore storage
     */
    public function commit()
    {
        if (!$this->currentToken) {
            throw new GenericError("you cannot commit without a token");
        }

        // Save all temporary layouts in permanent storage and update this
        // object's internals at the same time
        foreach ($this->tokenStorage->loadMultiple($this->currentToken->getToken(), $this->currentToken->getLayoutIdList()) as $layout) {
            $this->storage->update($layout);
            $this->layouts[$layout->getId()] = $layout;
        }

        $this->currentToken = null;
    }

    /**
     * Rollback session changes and restore storage
     */
    public function rollback()
    {
        if (!$this->currentToken) {
            throw new GenericError("you cannot rollback without a token");
        }

        // Reload the real layouts unchanged
        foreach ($this->storage->loadMultiple($this->currentToken->getLayoutIdList()) as $layout) {
            $this->layouts[$layout->getId()] = $layout;
        }

        $this->tokenStorage->deleteAll($this->currentToken->getToken());

        $this->currentToken = null;
    }

}
