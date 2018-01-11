<?php

namespace MakinaCorpus\Layout\Tests\Driver\Goat;

use Goat\Runner\RunnerInterface;
use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Context\DefaultTokenGenerator;
use MakinaCorpus\Layout\Driver\Goat\LayoutStorage;
use MakinaCorpus\Layout\Driver\Goat\TokenLayoutStorage;
use MakinaCorpus\Layout\Storage\LayoutStorageInterface;
use MakinaCorpus\Layout\Storage\TokenLayoutStorageInterface;
use MakinaCorpus\Layout\Tests\Unit\ComparisonTestTrait;
use PHPUnit\Framework\TestCase;
use Goat\Driver\PgSQL\ExtPgSQLConnection;
use Goat\Driver\Dsn;
use Goat\Hydrator\HydratorMap;
use Goat\Converter\ConverterMap;
use MakinaCorpus\Layout\Driver\Goat\LayoutUpdater;

/**
 * Basis for tests.
 */
abstract class AbstractLayoutTest extends TestCase
{
    use ComparisonTestTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markAsRisky();

        $runner = $this->getRunner();
        try {
            $transaction = $runner->startTransaction()->start();
            (new LayoutUpdater())->installSchema($runner, $transaction);
            $transaction->commit();
        } catch (\Throwable $e) {
            if ($transaction) {
                $transaction->rollback();
            }
            throw $e;
        }

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $runner = $this->getRunner();

        try {
            $transaction = $runner->startTransaction()->start();
            (new LayoutUpdater())->uninstallSchema($runner, $transaction);
            $transaction->commit();
        } catch (\Throwable $e) {
            if ($transaction) {
                $transaction->rollback();
            }
            throw $e;
        }
        return;

        // @todo remove all data
        foreach ($this->nodes as $node) {
            try {
                node_delete($node->id());
            } catch (\Exception $e) {
                // Pass and delete everything you can
            }
        }

        parent::tearDown();
    }

    /**
     * Create layout page context
     */
    protected function createPageContext() : Context
    {
        return new Context($this->createStorage(), $this->createTokenStorage(), null, null, new DefaultTokenGenerator());
    }

    /**
     * Create converter
     */
    private function createConverter() : ConverterMap
    {
        $map = new ConverterMap();

        foreach (ConverterMap::getDefautConverterMap() as $type => $data) {
            list($class, $aliases) = $data;

            $map->register($type, new $class(), $aliases);
        }

        return $map;
    }

    /**
     * Create object hydrator
     */
    private function createHydrator() : HydratorMap
    {
        $cacheDir = sys_get_temp_dir().'/'.uniqid('goat-test-');
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir)) {
                $this->markTestSkipped(sprintf("cannot create temporary folder %s", $cacheDir));
            }
        }

        return new HydratorMap($cacheDir);
    }

    /**
     * Get runner
     */
    protected function getRunner() : RunnerInterface
    {
        $connection = new ExtPgSQLConnection(new Dsn(getenv('EXT_PGSQL_DSN'), getenv('EXT_PGSQL_USERNAME'), getenv('EXT_PGSQL_PASSWORD')));
        $connection->setDebug(true);
        $connection->setConverter($this->createConverter());
        $connection->setHydratorMap($this->createHydrator());

        return $connection;
    }

    /**
     * Creates the tested storage instance
     */
    protected function createStorage() : LayoutStorageInterface
    {
        return new LayoutStorage($this->getRunner(), $this->createTypeRegistry());
    }

    /**
     * Creates the tested storage instance
     */
    protected function createTokenStorage() : TokenLayoutStorageInterface
    {
        return new TokenLayoutStorage($this->getRunner());
    }
}
