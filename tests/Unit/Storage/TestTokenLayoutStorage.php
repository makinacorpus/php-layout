<?php

namespace MakinaCorpus\Layout\Tests\Unit\Storage;

use MakinaCorpus\Layout\Controller\EditToken;
use MakinaCorpus\Layout\Error\InvalidTokenError;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Storage\TokenLayoutStorageInterface;

/**
 * Very specific version of layout storage, used only for editing purposes
 */
class TestTokenLayoutStorage implements TokenLayoutStorageInterface
{
    private $sequence = 1;
    private $tokens = [];
    private $layouts = [];

    /**
     * {@inheritdoc}
     */
    public function loadToken(string $token) : EditToken
    {
        if (!isset($this->tokens[$token])) {
            throw new InvalidTokenError();
        }

        return $this->tokens[$token];
    }

    /**
     * {@inheritdoc}
     */
    public function saveToken(EditToken $token)
    {
        $this->tokens[$token->getToken()] = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $token, int $id) : LayoutInterface
    {
        if (!isset($this->tokens[$token])) {
            throw new InvalidTokenError();
        }
        if (!isset($this->layouts[$token][$id])) {
            throw new InvalidTokenError();
        }

        return $this->layouts[$token][$id];
    }

    /**
     * {@inheritdoc}
     */
    public function loadMultiple(string $token, array $idList) : array
    {
        if (!isset($this->tokens[$token])) {
            throw new InvalidTokenError();
        }

        $ret = [];
        foreach ($idList as $id) {
            if (!isset($this->layouts[$token][$id])) {
                throw new InvalidTokenError();
            }

            $ret[$id] = $this->layouts[$token][$id];
        }

        return $ret;
    }

    /**
     * Delete all for the given token
     *
     * This will remain silent if the token does not exists
     *
     * @param string $token
     *   String token identifier
     *
     * @param string $token
     */
    public function deleteAll(string $token)
    {
        unset($this->tokens[$token]);
    }

    /**
     * Ensure that all items have a storage identifier
     *
     * @param ItemInterface $item
     */
    private function ensureIdentifiers(ItemInterface $item)
    {
        $id = $item->getStorageId();
        if (!$id) {
            $item->setStorageId($this->sequence++);
        }

        if ($item instanceof ContainerInterface) {
            foreach ($item->getAllItems() as $child) {
                $this->ensureIdentifiers($child);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $token, LayoutInterface $layout)
    {
        $this->ensureIdentifiers($layout->getTopLevelContainer());

        $this->layouts[$token][$layout->getId()] = $layout;
    }
}
