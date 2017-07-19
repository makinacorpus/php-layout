<?php

namespace MakinaCorpus\Layout\Storage;

use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Error\InvalidTokenError;

/**
 * Very specific version of layout storage, used only for editing purposes
 *
 * VERY IMPORTANT NOTE: no matter how you store objects (you could serialize
 * them or use any other storage mecanism) - you MUST give a tempory unique
 * identifier to every item in the tree, else the EditController implementation
 * will not be able to find items and containers in the tree.
 *
 * You also MUST propagate the layout identifier to all object either when
 * storing or when loading the item tree.
 */
interface TokenLayoutStorageInterface
{
    /**
     * Load edit token
     *
     * @param string $token
     *   String token identifier
     *
     * @return EditToken
     *
     * @throws InvalidTokenError
     */
    public function loadToken(string $token) : EditToken;

    /**
     * Save edit token
     *
     * @param EditToken $token
     *   Token to storage
     */
    public function saveToken(EditToken $token);

    /**
     * Load a single layout
     *
     * @param string $token
     *   String token identifier
     * @param int $id
     *   Layout identifier
     *
     * @return LayoutInterface
     *   Loaded layout
     *
     * @throws InvalidTokenError
     *   If the layout does not exists
     */
    public function load(string $token, int $id) : LayoutInterface;

    /**
     * Load multiple layouts
     *
     * @param string $token
     *   String token identifier
     * @param int[] $idList
     *   Layout identifiers
     *
     * @return LayoutInterface[]
     *   Same as load() but an array of it keyed by identifiers
     */
    public function loadMultiple(string $token, array $idList) : array;

    /**
     * Delete all for the given token
     *
     * This will remain silent if the token does not exists
     *
     * @param string $token
     *   String token identifier
     */
    public function deleteAll(string $token);

    /**
     * Update a single layout
     *
     * @param string $token
     *   String token identifier
     * @param LayoutInterface $layout
     *   Layout instance to update
     *
     * @throws InvalidTokenError
     *   If the menu does not exist
     */
    public function update(string $token, LayoutInterface $layout);

    /**
     * Remove a single layout
     *
     * Be silent if layout was not in the token
     *
     * @param string $token
     *   String token identifier
     * @param int $id
     *   Layout instance to remove
     */
    public function remove(string $token, int $id);
}
