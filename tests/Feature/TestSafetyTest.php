<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/**
 * The suite runs against the shared QA database. These prove the guards in
 * TestCase / CreatesApplication hold, using probes that are harmless even if a
 * guard were broken (a missing table, a read-only command, a dummy class).
 */
class TestSafetyTest extends TestCase
{
    private const PROBE_EMAIL = 'gv9566-rollback-probe@example.invalid';

    public function test_ddl_and_truncate_are_refused_before_reaching_mysql(): void
    {
        foreach (['TRUNCATE TABLE __gv9566_missing_table', 'DROP TABLE __gv9566_missing_table', 'ALTER TABLE __gv9566_missing_table ADD x INT'] as $sql) {
            try {
                DB::statement($sql);
                $this->fail("Not refused: $sql");
            } catch (RuntimeException $e) {
                $this->assertStringStartsWith('Refused in tests', $e->getMessage(), $sql);
            }
        }
    }

    public function test_destructive_artisan_commands_are_refused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Refused in tests');

        $this->artisan('migrate:status');
    }

    public function test_database_wiping_traits_are_refused(): void
    {
        $this->expectException(RuntimeException::class);

        TestCase::assertNoDatabaseWipingTraits(DatabaseWipingProbe::class);
    }

    public function test_a_write_inside_a_test_is_visible_during_the_test(): void
    {
        DB::table('password_reset_tokens')->insert(['email' => self::PROBE_EMAIL, 'token' => 'x', 'created_at' => now()]);

        $this->assertTrue(DB::table('password_reset_tokens')->where('email', self::PROBE_EMAIL)->exists());
    }

    /** @depends test_a_write_inside_a_test_is_visible_during_the_test */
    public function test_that_write_was_rolled_back_afterwards(): void
    {
        $this->assertFalse(DB::table('password_reset_tokens')->where('email', self::PROBE_EMAIL)->exists());
    }
}

/** Never run: only its trait list is inspected. */
class DatabaseWipingProbe
{
    use RefreshDatabase;
}
