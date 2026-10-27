<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // Every test runs in a transaction that is rolled back afterwards, so the
    // shared database is left exactly as it was found.
    use DatabaseTransactions;

    /** Commands that rebuild or empty the database. */
    private const DESTRUCTIVE_COMMANDS = '/^(migrate(:.*)?|db:wipe|db:seed|schema:dump)$/';

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        // Checked before parent::setUp(), which is where these traits would run
        // migrate:fresh and wipe the database.
        static::assertNoDatabaseWipingTraits(static::class);

        parent::setUp();

        // Set up any global test configuration
        $this->withoutVite();
    }

    /** Refuse any class using a trait that rebuilds or empties the database. */
    public static function assertNoDatabaseWipingTraits(string $class): void
    {
        $forbidden = array_intersect(
            [RefreshDatabase::class, DatabaseMigrations::class, DatabaseTruncation::class],
            class_uses_recursive($class)
        );

        if ($forbidden) {
            throw new RuntimeException(
                $class . ' uses ' . implode(', ', array_map('class_basename', $forbidden))
                . ', which wipes the shared database. Remove it: TestCase already rolls every test back.'
            );
        }
    }

    public function artisan($command, $parameters = [])
    {
        if (preg_match(self::DESTRUCTIVE_COMMANDS, strtok(trim((string) $command), ' '))) {
            throw new RuntimeException("Refused in tests: artisan {$command} would change the shared database.");
        }

        return parent::artisan($command, $parameters);
    }
}
