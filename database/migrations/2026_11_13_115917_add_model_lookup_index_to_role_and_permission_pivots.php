<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The baseline migrations created model_has_roles and model_has_permissions without
 * the (model_id, model_type) index that spatie/laravel-permission ships, so every
 * "roles of this user" lookup (hasRole, whereHas('roles'), eager-loaded ->roles)
 * scanned the whole pivot table. Installs created from the package migration already
 * have an index led by model_id; those are left alone.
 */
return new class extends Migration
{
    private const TABLES = ['model_has_roles', 'model_has_permissions'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table) || $this->hasModelIdIndex($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->index(['model_id', 'model_type'], $this->indexName($table));
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table) || !$this->hasIndexNamed($table, $this->indexName($table))) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex($this->indexName($table));
            });
        }
    }

    // Own name, so down() never drops an index this migration did not create.
    private function indexName(string $table): string
    {
        return "{$table}_model_lookup_index";
    }

    private function hasModelIdIndex(string $table): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('column_name', 'model_id')
            ->where('seq_in_index', 1)
            ->exists();
    }

    private function hasIndexNamed(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
