<?php

declare(strict_types=1);

namespace MakinaCorpus\Layout\Driver\Goat;

use Goat\Bundle\Installer\Updater;
use Goat\Runner\RunnerInterface;
use Goat\Runner\Transaction;

class LayoutUpdater extends Updater
{
    /**
     * {@inheritdoc}
     */
    public function installSchema(RunnerInterface $runner, Transaction $transaction)
    {
        $runner->query(<<<EOT
CREATE TABLE layout (
    id SERIAL PRIMARY KEY
);
EOT
        );

        $runner->query(<<<EOT
CREATE TABLE layout_data (
    id SERIAL PRIMARY KEY,
    parent_id INTEGER DEFAULT NULL,
    layout_id INTEGER NOT NULL,
    item_type VARCHAR(128) NOT NULL,
    item_id VARCHAR(128) NOT NULL,
    style VARCHAR(128) NOT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    options TEXT DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES layout_data (id) ON DELETE CASCADE,
    FOREIGN KEY (layout_id) REFERENCES layout (id) ON DELETE CASCADE
);
EOT
        );

        $runner->query(<<<EOT
CREATE TABLE layout_token (
    token VARCHAR(255) NOT NULL PRIMARY KEY,
    data TEXT DEFAULT NULL
);
EOT
        );

        $runner->query(<<<EOT
CREATE TABLE layout_token_layout (
    token VARCHAR(255) NOT NULL,
    layout_id INTEGER NOT NULL,
    data TEXT DEFAULT NULL,
    PRIMARY KEY (token, layout_id),
    FOREIGN KEY (token) REFERENCES layout_token (token) ON DELETE CASCADE,
    FOREIGN KEY (layout_id) REFERENCES layout (id) ON DELETE CASCADE
);
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    public function uninstallSchema(RunnerInterface $runner, Transaction $transaction)
    {
        $runner->query("DROP TABLE layout_token_layout");
        $runner->query("DROP TABLE layout_token");
        $runner->query("DROP TABLE layout_data");
        $runner->query("DROP TABLE layout");
    }
}
