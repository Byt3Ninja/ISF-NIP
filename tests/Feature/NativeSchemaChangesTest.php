<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * With doctrine/dbal installed, Laravel 10 sends every ->change() through DBAL
 * unless told otherwise, and DBAL cannot change an enum column. On an empty
 * database that stopped `artisan migrate` at
 * 2024_12_01_212635_add_group_to_questions_bank with:
 * Unknown column type "enum" requested.
 *
 * AppServiceProvider keeps column changes native. This compiles that
 * migration's change without running it, so nothing is altered.
 */
class NativeSchemaChangesTest extends TestCase
{
    public function test_an_enum_column_change_compiles_natively_with_doctrine_dbal_installed(): void
    {
        $this->assertTrue(
            class_exists(\Doctrine\DBAL\Connection::class),
            'doctrine/dbal is no longer installed, so column changes are native anyway and this test can go.'
        );

        $connection = DB::connection();
        $connection->useDefaultSchemaGrammar();

        $blueprint = new Blueprint('questions_bank', function (Blueprint $table) {
            $table->enum('group', ['quiz', 'survey', 'assessment'])->change();
        });

        $this->assertSame(
            ["alter table `questions_bank` modify `group` enum('quiz', 'survey', 'assessment') not null"],
            $blueprint->toSql($connection, $connection->getSchemaGrammar())
        );
    }
}
