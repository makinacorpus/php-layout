<?php

namespace MakinaCorpus\Layout\Context;

use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Error\InvalidTokenError;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Storage\LayoutStorageInterface;
use MakinaCorpus\Layout\Storage\TokenLayoutStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Context is the main API entry point for users
 *
 * It is able to transparently load temporary in-edition layouts.
 *
 * Execution flow:
 *
 *   - On the kernel request event, request is parsed for an edit token then
 *     populated within this context instance. It is better for performances
 *     that this happen before any controller argument resolver happens, it
 *     will then avoid a non-temporary layout version preload for nothing, also
 *     it's important that the argument resolvers to be able to tell if there
 *     is an existing token or not.
 *
 *   - Arbitrarily at runtime, any piece of business code using this object may
 *     add new layouts to this context.
 *
 *   - Please note that even if it can, business code should probably not
 *     manually call addLayout() or addLayoutList() but implementations should
 *     probably rely on the CollectLayoutEvent instead.
 *
 *   - The EditToken instance, if set, will automatically add those layouts into
 *     the context and transparently trigger their temporary versions loading if
 *     relevant (a non-editable layout can not have a temporary version).
 *
 *   - Determining if layouts are editable or not must happen using the security
 *     voters or any implementation that will act upon the Symfony's security
 *     AuthorizationCheckerInterface::isGranted() method.
 *
 *   - Final rendering MUST happen AFTER token is set and every layout is loaded
 *     and MUST use this context to load layouts to display: hence the temporary
 *     versions will be correctly selected automatically for rendering.
 *
 * @todo testing
 *   - layout lazy load
 *   - reset() method, should also reset loaded layouts when temporary
 *   - setToken() (with and without already set)
 *   - getToken() (with and without already set)
 *   - setToken() with editable: load layouts first, then reload them after, ensure they are temporary
 *   - isEditable() with and without manual toggleEditable() being called: ensure authorization checker is called or not
 *   - snapshot() commit() and rollback() are trivial to test
 */
final class Context
{
    /**
     * Layout edit permission string
     */
    const PERMISSION_EDIT = 'edit';

    private $authorizationChecker;
    private $editableIndex = [];
    private $editToken;
    private $eventDispatcher;
    private $layoutIndex = [];
    private $layoutLoaded = false;
    private $layouts;
    private $layoutStorage;
    private $tokenGenerator;
    private $tokenStorage;

    /**
     * Default constructor
     *
     * @param LayoutStorageInterface $storage
     * @param TokenLayoutStorageInterface $tokenStorage
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(
        LayoutStorageInterface $storage,
        TokenLayoutStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker = null,
        EventDispatcherInterface $eventDispatcher = null,
        TokenGeneratorInterface $tokenGenerator = null
    ) {
        $this->layoutStorage = $storage;
        $this->tokenStorage = $tokenStorage;

        if (!$tokenGenerator) {
            $tokenGenerator = new DefaultTokenGenerator();
        }
        $this->tokenGenerator = $tokenGenerator;

        if ($authorizationChecker) {
            $this->authorizationChecker = $authorizationChecker;
        }
        if ($this->eventDispatcher) {
            $this->eventDispatcher = $eventDispatcher;
        }
    }

    /**
     * Load all layouts
     */
    private function loadLayouts()
    {
        if ($this->layoutLoaded) {
            return;
        }

        $this->layouts = [];
        $this->layoutLoaded = true;

        $fromToken = [];
        $fromStorage = [];

        foreach ($this->layoutIndex as $layoutId) {
            if ($this->editToken && $this->editToken->contains($layoutId)) {
                $fromToken[] = $layoutId;
            } else {
                $fromStorage[] = $layoutId;
            }
        }

        if ($fromToken) {
            // No need to check for token existing, it is conditionned by the
            // if upper, if there are identifiers in the $fromToken variable
            // then we have a token
            $this->layouts = $this->tokenStorage->loadMultiple($this->editToken->getToken(), $fromToken);
        }
        if ($fromStorage) {
            $this->layouts += $this->layoutStorage->loadMultiple($fromStorage);
        }
    }

    /**
     * Create edit token
     *
     * @param string[] $layoutIdList
     *   List of layouts to switch to edit mode, empty array means edit them all
     * @param string[] $additional
     *   Arbitrary information to store and fetch along for security or other
     *   business purpose
     *
     * @return EditToken
     */
    public function createEditToken(array $layoutIdList = [], array $additional = [])
    {
        if ($this->editToken) {
            throw new GenericError("you cannot create a new token, context is already in edit mode");
        }

        $allowed = array_keys(array_filter($this->editableIndex));
        if ($layoutIdList) {
            $layoutIdList = array_intersect($allowed, $layoutIdList);
        } else {
            $layoutIdList = $allowed;
        }

        $this->editToken = new EditToken($this->tokenGenerator->create(), $layoutIdList, $additional);
        $this->tokenStorage->saveToken($this->editToken);

        // We cannot use the snapshot() method on the edit token creation
        // because layouts are not stored in the token storage yet, and it
        // will attempt to load them from there, force them to be loaded
        // from the permanent cache and store them
        foreach ($this->editToken->getLayoutIdList() as $id) {
            if (isset($this->layouts[$id])) {
                $layout = $this->layouts[$id];
            } else {
                $layout = $this->layoutStorage->load($id);
            }
            $this->tokenStorage->update($this->editToken->getToken(), $layout);
        }


        return $this->editToken;
    }

    /**
     * Get token storage
     *
     * @return LayoutStorageInterface
     */
    public function getLayoutStorage() : LayoutStorageInterface
    {
        return $this->layoutStorage;
    }

    /**
     * Get token storage
     *
     * @return TokenLayoutStorageInterface
     */
    public function getTokenStorage() : TokenLayoutStorageInterface
    {
        return $this->tokenStorage;
    }

    /**
     * Reset current token
     */
    public function resetToken()
    {
        $this->layouts = [];
        $this->layoutLoaded = false;
        $this->editToken = null;
    }

    /**
     * Are they contextual layouts
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return empty($this->layoutIndex);
    }

    /**
     * Is there any editable layout in this context
     *
     * @return bool
     */
    public function containsEditableLayouts() : bool
    {
        return !empty($this->editableIndex);
    }

    /**
     * Is layout editable
     */
    public function isEditable(LayoutInterface $layout)
    {
        $id = $layout->getId();

        // Layout might have been manually set to editable by any business code
        // case in which using the authorization checker is useless, and should
        // be bypassed.
        if (isset($this->editableIndex[$id])) {
            return $this->editableIndex[$id];
        }

        if ($this->authorizationChecker) {
            return $this->authorizationChecker->isGranted(self::PERMISSION_EDIT, $layout);
        }

        return false;
    }

    /**
     * Add a layout in context
     *
     * @param int $id
     */
    public function addLayout(int $id)
    {
        $this->addLayoutList([$id]);
    }

    /**
     * Add one or more layouts in current context
     *
     * @param int[] $idList
     */
    public function addLayoutList(array $idList)
    {
        $this->layoutIndex = array_unique(array_merge($this->layoutIndex, $idList));
    }

    /**
     * Toggle layouts as editable
     *
     * @param int[] $layoutIdList
     * @param bool $editable
     */
    public function toggleEditable(array $layoutIdList, bool $editable = true)
    {
        foreach ($layoutIdList as $id) {
            if (!in_array($id, $this->layoutIndex)) {
                throw new GenericError(sprintf("layout %d is not in context", $id));
            }

            $this->editableIndex[$id] = $editable;
        }
    }

    /**
     * Get a single layout from context
     *
     * @param int $id
     *
     * @return LayoutInterface
     *   Loaded layout or temporary layout
     */
    public function getLayout($id) : LayoutInterface
    {
        $this->loadLayouts();

        if (!isset($this->layouts[$id])) {
            throw new GenericError(sprintf("layout %d is not in current context", $id));
        }

        return $this->layouts[$id];
    }

    /**
     * Get a single layout from context
     *
     * @return LayoutInterface[]
     *   Loaded layouts or temporary layouts, keys are their identifiers
     */
    public function getAllLayouts() : array
    {
        $this->loadLayouts();

        return $this->layouts;
    }

    /**
     * Does this context has a token
     *
     * @return bool
     */
    public function hasToken() : bool
    {
        return null !== $this->editToken;
    }

    /**
     * Set current edit token
     *
     * @param string $tokenString
     *   Token hash string
     *
     * @throws GenericError
     *   If the token was already set
     * @throws InvalidTokenError
     *   If token is invalid
     *
     * @return EditToken
     *   The loaded edit token
     */
    public function setToken(string $tokenString) : EditToken
    {
        if ($this->editToken) {
            throw new GenericError("context token was already set, did you forget to call reset()?");
        }

        $this->editToken = $this->tokenStorage->loadToken($tokenString);

        // If some layouts are set in the current token, reset them from the
        // preloaded context layouts
        foreach ($this->editToken->getLayoutIdList() as $id) {
            unset($this->layouts[$id]);
        }

        return $this->editToken;
    }

    /**
     * Get edit token
     *
     * @return EditToken
     *   Loaded edit token
     *
     * @throws GenericError
     *   If there is no token set
     */
    public function getToken() : EditToken
    {
        if (!$this->editToken) {
            throw new GenericError("context token was not set, did you forget to call setToken()?");
        }

        return $this->editToken;
    }

    /**
     * Save current temporary layouts state
     */
    public function snapshot()
    {
        if (!$this->editToken) {
            throw new GenericError("context token was not set, did you forget to call setToken()?");
        }

        // This will only store layouts if they already have been loaded, if not
        // it means that they cannot have been modified by the user, hence there
        // is no need in storing them if unchanged
        foreach ($this->editToken->getLayoutIdList() as $id) {
            if (isset($this->layouts[$id])) {
                $this->tokenStorage->update($this->editToken->getToken(), $this->layouts[$id]);
            }
        }
    }

    /**
     * Persist all temporary layouts to database and drop the token
     */
    public function commit()
    {
        if (!$this->editToken) {
            throw new GenericError("context token was not set, did you forget to call setToken()?");
        }

        // Save all temporary layouts in permanent storage and update this
        // object's internals at the same time
        foreach ($this->tokenStorage->loadMultiple($this->editToken->getToken(), $this->editToken->getLayoutIdList()) as $layout) {
            $this->layoutStorage->update($layout);
        }

        $this->tokenStorage->deleteAll($this->editToken->getToken());
        $this->resetToken();
    }

    /**
     * Rollback all temporary layouts to their original state and drop the token
     */
    public function rollback()
    {
        if (!$this->editToken) {
            throw new GenericError("context token was not set, did you forget to call setToken()?");
        }

        $this->tokenStorage->deleteAll($this->editToken->getToken());
        $this->resetToken();
    }
}
