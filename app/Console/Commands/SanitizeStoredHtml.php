<?php

namespace App\Console\Commands;

use App\Services\HtmlSanitizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time backfill command that strips JavaScript/XSS vectors from HTML that
 * was stored before input sanitization was added to the relevant controllers.
 *
 * It targets only the columns that are rendered raw ({!! !!}) in Blade views:
 *   - training_contents.content      (TrainingController@storecontent)
 *   - cycle_custom_pages.content     (CycleCustomPagesController add/update)
 *   - questions_bank.question        (QuestionsBankController store/update)
 *   - documentations.content_ar/en   (DocumentationController store/update)
 *   - organization_custom_sections.content          (OrganizationCustomSectionService)
 *   - landing_page_custom_sections.content/_ar      (LandingPageCustomSectionService)
 *
 * The command is idempotent: it only writes a row when the sanitized value
 * actually differs from the stored value, so re-running it is a no-op. Run with
 * --dry-run first to preview the number of rows that would change.
 */
class SanitizeStoredHtml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:sanitize-stored-html {--dry-run : Report what would change without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill: strip stored JavaScript/XSS from raw-rendered HTML columns';

    /**
     * Tables and their HTML columns to sanitize. Each entry sanitizes one or
     * more columns keyed by the table's primary key.
     */
    protected array $targets = [
        ['table' => 'training_contents',  'key' => 'id', 'columns' => ['content']],
        ['table' => 'cycle_custom_pages', 'key' => 'id', 'columns' => ['content']],
        ['table' => 'questions_bank',     'key' => 'id', 'columns' => ['question']],
        ['table' => 'documentations',     'key' => 'id', 'columns' => ['content_ar', 'content_en']],
        ['table' => 'organization_custom_sections', 'key' => 'id', 'columns' => ['content']],
        ['table' => 'landing_page_custom_sections', 'key' => 'id', 'columns' => ['content', 'content_ar']],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info('Starting: ' . $this->signature . ($dryRun ? ' [DRY RUN]' : ''));

        $grandScanned = 0;
        $grandChanged = 0;

        foreach ($this->targets as $target) {
            $table   = $target['table'];
            $key     = $target['key'];
            $columns = $target['columns'];

            if (! $this->tableIsReady($table, $key, $columns)) {
                continue;
            }

            $scanned = 0;
            $changed = 0;

            DB::table($table)
                ->select(array_merge([$key], $columns))
                ->orderBy($key)
                ->chunkById(500, function ($rows) use ($table, $key, $columns, $dryRun, &$scanned, &$changed) {
                    foreach ($rows as $row) {
                        $scanned++;
                        $updates = [];

                        foreach ($columns as $column) {
                            $original = $row->{$column};
                            if ($original === null || $original === '') {
                                continue;
                            }
                            $clean = HtmlSanitizer::clean($original);
                            if ($clean !== $original) {
                                $updates[$column] = $clean;
                            }
                        }

                        if (! empty($updates)) {
                            $changed++;
                            if (! $dryRun) {
                                DB::table($table)->where($key, $row->{$key})->update($updates);
                            }
                        }
                    }
                }, $key);

            $verb = $dryRun ? 'would change' : 'changed';
            $this->line(sprintf(
                '  %-22s scanned: %-6d %s: %d',
                $table,
                $scanned,
                $verb,
                $changed
            ));

            $grandScanned += $scanned;
            $grandChanged += $changed;
        }

        $verb = $dryRun ? 'would change' : 'changed';
        $this->info(sprintf('Done. Total scanned: %d, %s: %d', $grandScanned, $verb, $grandChanged));

        if ($dryRun && $grandChanged > 0) {
            $this->comment('Re-run without --dry-run to apply these changes.');
        }

        return self::SUCCESS;
    }

    /**
     * Verify the table and required columns exist before touching them, so a
     * schema difference in any environment skips gracefully instead of erroring.
     */
    protected function tableIsReady(string $table, string $key, array $columns): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
            $this->warn("  Skipping {$table}: table not found.");
            return false;
        }

        foreach (array_merge([$key], $columns) as $column) {
            if (! \Illuminate\Support\Facades\Schema::hasColumn($table, $column)) {
                $this->warn("  Skipping {$table}: missing column '{$column}'.");
                return false;
            }
        }

        return true;
    }
}
