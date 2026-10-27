<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use RuntimeException;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->guardAgainstDestructiveStatements($app);

        return $app;
    }

    /**
     * The test suite shares the database used for QA, and every test runs inside
     * a transaction that is rolled back (see TestCase). MySQL commits DDL and
     * TRUNCATE implicitly, so those would escape the rollback and destroy real
     * data -- refuse them on every connection before they reach the server.
     */
    protected function guardAgainstDestructiveStatements($app): void
    {
        $guard = function ($query) {
            if (preg_match('/^\s*(truncate|drop|alter|create|rename)\b/i', (string) $query)) {
                throw new RuntimeException(
                    'Refused in tests: "' . strtok(trim((string) $query), "\n") . '". '
                    . 'It would commit outside the test transaction and change the shared database.'
                );
            }
        };

        // Connections already opened while the app booted...
        foreach ($app['db']->getConnections() as $connection) {
            $connection->beforeExecuting($guard);
        }

        // ...and any opened (or re-opened after a purge) later.
        foreach (array_keys($app['config']['database.connections']) as $name) {
            $app['db']->extend($name, function ($config, $name) use ($app, $guard) {
                $connection = $app['db.factory']->make($config, $name);
                $connection->beforeExecuting($guard);

                return $connection;
            });
        }
    }
}
