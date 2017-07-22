<?php

namespace MakinaCorpus\Layout\Controller;

use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Error\GenericError;
use MakinaCorpus\Layout\Error\SecurityError;

/**
 * Represents runtime page context: all layouts arbitrary discovered during page
 * construction will be given to this context. At rendering time, you can use
 * this instance to fetch all layouts.
 *
 * When the user switches to edit mode, you may use this instance to create the
 * edition context.
 *
 * @codeCoverageIgnore
 * @deprecated
 *   Keeping this because there's a few method I still need to port in the
 *   new context implementation
 */
final class LegacyContext
{
    /**
     * Add layout to current token
     *
     * @param array $layoutId
     */
    public function addLayoutToCurrentToken(array $layoutId = [])
    {
        if ($this->currentToken) {
            throw new GenericError("you cannot create a new token, context is already in edit mode");
        }

        $layout = $this->storage->load($layoutId);

        if ($this->currentToken->contains($layout)) {
            return;
        }

        $allowed = array_keys(array_filter($this->editableIndex));
        $layoutId = array_intersect($allowed, $layoutId);

        // I am so, so, sorry for this
        $this->tokenStorage->update($this->currentToken->getToken(), $layout);
        $this->currentToken = new EditToken($this->currentToken->getToken(), array_merge($this->currentToken->getLayoutIdList(), [$layoutId]));
    }

    /**
     * Partially commit the current token
     *
     * @param LayoutInterface[] $layouts
     */
    public function partialCommit(array $layouts)
    {
        if (!$this->currentToken) {
            throw new GenericError("you cannot commit without a token");
        }

        $current = $this->currentToken->getLayoutIdList();
        $removed = [];

        foreach ($layouts as $layout) {
            if (!$this->currentToken->contains($layout)) {
                throw new SecurityError(sprintf("token does not contain the %s layout", $layout->getId()));
            }

            $removed[] = $layoutId = $layout->getId();

            $temporaryLayout = $this->tokenStorage->load($this->currentToken, $layoutId);
            $this->storage->update($temporaryLayout);
            $this->tokenStorage->remove($this->currentToken, $layoutId);
        }

        $this->currentToken = new EditToken($this->currentToken->getToken(), array_diff($current, $removed));
    }

    /**
     * Partially rollback the current token
     *
     * @param LayoutInterface[] $layouts
     */
    public function partialRollback(array $layouts)
    {
        if (!$this->currentToken) {
            throw new GenericError("you cannot commit without a token");
        }

        $current = $this->currentToken->getLayoutIdList();
        $removed = [];

        foreach ($layouts as $layout) {
            $removed[] = $layoutId = $layout->getId();

            if (!$this->currentToken->contains($layout)) {
                $this->tokenStorage->remove($this->currentToken, $layoutId);
            }
        }

        $this->currentToken = new EditToken($this->currentToken->getToken(), array_diff($current, $removed));
    }
}
