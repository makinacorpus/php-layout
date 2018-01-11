<?php

namespace MakinaCorpus\Layout\Driver\Goat;

use Goat\Runner\RunnerInterface;
use MakinaCorpus\Layout\Context\EditToken;
use MakinaCorpus\Layout\Error\InvalidTokenError;
use MakinaCorpus\Layout\Grid\ContainerInterface;
use MakinaCorpus\Layout\Grid\ItemInterface;
use MakinaCorpus\Layout\Storage\LayoutInterface;
use MakinaCorpus\Layout\Storage\TokenLayoutStorageInterface;
use MakinaCorpus\Layout\Storage\DefaultLayout;

/**
 * Layout database storage
 *
 * In order to avoid identifier conflicts with the layout permanent storage
 * we are going to store negative identifiers in temporary storage (and why
 * not actually?).
 */
class TokenLayoutStorage implements TokenLayoutStorageInterface
{
    private $database;

    /**
     * Default constructor
     */
    public function __construct(RunnerInterface $database)
    {
        $this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    public function loadToken(string $token) : EditToken
    {
        $data = $this->database->query("select data from layout_token where token = $*", [$token])->fetchField();

        if (!$data) {
            throw new InvalidTokenError(sprintf("%s: token does not exists", $token));
        }

        $instance = @unserialize(base64_decode($data));

        // @codeCoverageIgnoreStart
        // This mean data is broken in the database side
        if (!$instance || !$instance instanceof EditToken) {
            $this->database->query("delete from layout_token where token = $*", [$token]);

            throw new InvalidTokenError(sprintf("%s: token does not exists", $token));
        }
        // @codeCoverageIgnoreEnd

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function saveToken(EditToken $token)
    {
        $exists = $this->database->query("select 1 from layout_token where token = $*", [$token->getToken()])->fetchField();

        if ($exists) {
            $this
                ->database
                ->update('layout_token')
                ->sets([
                    'data' => base64_encode(serialize($token)),
                ])
                ->condition('token', $token->getToken())
                ->execute()
            ;
        } else {
            $this
                ->database
                ->insertValues('layout_token')
                ->values([
                    'token' => $token->getToken(),
                    'data' => base64_encode(serialize($token)),
                ])
                ->execute()
            ;
        }
    }

    /**
     * Recursively ensure that everyone has an identifier
     *
     * @param int $layoutId
     * @param ItemInterface $item
     * @param int[] $done
     * @param int $current
     */
    private function ensureEveryoneHasIdentifiers(int $layoutId, ItemInterface $item, &$done)
    {
        $id = $item->getStorageId() ?: 0;

        // Circular dependency breaker
        if (isset($done[$id])) {
            return;
        }

        if (!$id) {
            $id = rand(PHP_INT_MIN, 1);
            $item->setStorageId($layoutId, $id, $item->isPermanent());
        }
        $done[$id] = $id;

        // This is a top-bottom traversal, we need containers to be saved
        // before their children
        if ($item instanceof ContainerInterface) {
            foreach ($item->getAllItems() as $child) {
                $this->ensureEveryoneHasIdentifiers($layoutId, $child, $done);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $token, int $id) : LayoutInterface
    {
        return $this->loadMultiple($token, [$id])[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function loadMultiple(string $token, array $idList) : array
    {
        $ret = [];

        if (!$idList) {
            // Do the security check for the token itself
            $this->loadToken($token);

            return $ret;
        }

        $rows = $this
            ->database
            ->select('layout_token_layout')
            ->column('layout_id')
            ->column('data')
            ->condition('token', $token)
            ->condition('layout_id', $idList)
            ->execute()
        ;

        // Re-key data
        $data = [];
        /** @var \Goat\Mapper\Entity\DefaultEntity $row */
        foreach ($rows as $row) {
            $data[$row['layout_id']] = $row['data'];
        }

        foreach ($idList as $id) {

            if (!isset($data[$id])) {
                throw new InvalidTokenError(sprintf("%s, %s: layout does not exists", $token, $id));
            }

            $instance = @unserialize(base64_decode($data[$id]));

            // @codeCoverageIgnoreStart
            // This mean data is broken in the database side
            if (!$instance || !$instance instanceof DefaultLayout) {
                $this->database->query("delete from layout_token_layout where token = $* and layout_id = $*", [$token, $id]);

                throw new InvalidTokenError(sprintf("%s, %s: token does not exists", $token, $id));
            }
            // @codeCoverageIgnoreEnd

            $ret[$id] = $instance;
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAll(string $token)
    {
        // Let the ON DELETE CASCADE do its job naturaly
        $this->database->query("delete from layout_token where token = $*", [$token]);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $token, LayoutInterface $layout)
    {
        $breaker = [];

        // Skip top-level container which should never have an identifier
        foreach ($layout->getTopLevelContainer()->getAllItems() as $item) {
            $this->ensureEveryoneHasIdentifiers($layout->getId(), $item, $breaker);
        }

        $exists = $this->database->query("select 1 from layout_token_layout where token = $* and layout_id = $*", [$token, $layout->getId()])->fetchField();

        if ($exists) {
            $this
                ->database
                ->update('layout_token_layout')
                ->sets([
                    'data' => base64_encode(serialize($layout)),
                ])
                ->condition('token', $token)
                ->condition('layout_id', $layout->getId())
                ->execute()
            ;
        } else {
            $this
                ->database
                ->insertValues('layout_token_layout')
                ->values([
                    'token' => $token,
                    'layout_id' => $layout->getId(),
                    'data' => base64_encode(serialize($layout)),
                ])
                ->execute()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(string $token, int $id)
    {
        $this
            ->database
            ->delete('layout_token_layout')
            ->condition('token', $token)
            ->condition('layout_id', $id)
            ->execute()
        ;
    }
}
