<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Storage\LayoutInterface;

/**
 * Edition token, this object is meant to be stored at the backend level
 * for security purposes: when a user enters in edit mode, a token is generated
 * and serves as an identifier for an "edition context" in which all editable
 * layouts from this context are referenced.
 *
 * On asynchronous edit request (AJAX or any other) this object will serve the
 * purpose of checking the edition context exists server side, and matches the
 * token.
 */
final class EditToken
{
    private $additional = [];
    private $layoutIdList = [];
    private $tokenStorage;
    private $token;

    /**
     * Default constructor
     *
     * @param string $token
     *   Security token
     * @param int[] $idList
     *   Layout identifier list
     * @param string[] $additional
     *   Arbitrary information to store and fetch along for security or other
     *   business purpose
     */
    public function __construct(string $token, array $idList, array $additional = [])
    {
        $this->token = $token;
        $this->layoutIdList = $idList;
        $this->additional = $additional;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken() : string
    {
        return $this->token;
    }

    /**
     * Get layout identifier list
     *
     * @return int[]
     */
    public function getLayoutIdList() : array
    {
        return $this->layoutIdList;
    }

    /**
     * Is layout editable in this context
     *
     * @param LayoutInterface $layout
     *
     * @return bool
     */
    public function contains(LayoutInterface $layout) : bool
    {
        return in_array($layout->getId(), $this->layoutIdList);
    }

    /**
     * Arbitrary get an additional value
     *
     * @param string $key
     *   Arbitrary data key
     *
     * @return string $value
     *   Will be an empty string if no value
     */
    public function getValue(string $key) : string
    {
        return $this->additional[$key] ?? '';
    }

    /**
     * Match any additional value
     *
     * @param string $key
     *   Arbitrary data key
     * @param string $value
     *   Value to match, null will never match
     *
     * @return bool
     */
    public function matchValue(string $key, $value) : bool
    {
        return null !== $value && ($this->additional[$key] ?? null) == (string)$value;
    }
}
