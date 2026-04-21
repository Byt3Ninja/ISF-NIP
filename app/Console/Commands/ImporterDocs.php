<?php

namespace App\Console\Commands;

use App\Services\Importer\ImporterFieldSchema;
use Illuminate\Console\Command;

/**
 * Regenerates the field-reference table embedded in IMPORTER_MODULE.md
 * from the single source of truth in ImporterFieldSchema.
 *
 * Re-run this command after any schema change. Do not edit the
 * generated block by hand.
 */
class ImporterDocs extends Command
{
    protected $signature   = 'importer:docs {--path=docs/developer_docs/IMPORTER_MODULE.md}';
    protected $description = 'Regenerate the field-reference table inside IMPORTER_MODULE.md.';

    private const BEGIN_MARKER = '<!-- BEGIN: AUTO-FIELDS -->';
    private const END_MARKER   = '<!-- END: AUTO-FIELDS -->';

    public function handle(): int
    {
        $path = base_path($this->option('path'));
        if (!file_exists($path)) {
            $this->error("Not found: {$path}");
            return self::FAILURE;
        }

        $md = file_get_contents($path);
        if (!str_contains($md, self::BEGIN_MARKER) || !str_contains($md, self::END_MARKER)) {
            $this->error('Marker block not found in target document.');
            return self::FAILURE;
        }

        $table = $this->renderFieldTable();
        $pattern = '/' . preg_quote(self::BEGIN_MARKER, '/') . '.*?' . preg_quote(self::END_MARKER, '/') . '/s';
        $replacement = self::BEGIN_MARKER . "\n\n" . $table . "\n\n" . self::END_MARKER;

        $new = preg_replace($pattern, $replacement, $md);
        file_put_contents($path, $new);

        $count = count(ImporterFieldSchema::all());
        $this->info("Regenerated {$path} with {$count} fields.");
        return self::SUCCESS;
    }

    private function renderFieldTable(): string
    {
        $rows   = [];
        $rows[] = '| Key | Section | Required | EN Label | AR Label | Validator | Target |';
        $rows[] = '|---|---|---|---|---|---|---|';
        foreach (ImporterFieldSchema::all() as $key => $f) {
            $t      = $f['target'] ?? [];
            $target = ($t['table'] ?? '') . '.' . ($t['column'] ?? '');
            if (!empty($t['team_member'])) {
                $target .= ' [TeamMember=' . $t['team_member'] . ']';
            }
            if (isset($t['year_offset'])) {
                $target .= ' [year+' . $t['year_offset'] . ']';
            }

            $rows[] = sprintf(
                '| `%s` | %s | %s | %s | %s | `%s` | `%s` |',
                $key,
                $f['section'] ?? '',
                !empty($f['required']) ? 'yes' : 'no',
                str_replace('|', '\\|', (string) ($f['label_en'] ?? '')),
                str_replace('|', '\\|', (string) ($f['label_ar'] ?? '')),
                (string) ($f['validator'] ?? ''),
                $target
            );
        }
        return implode("\n", $rows);
    }
}
