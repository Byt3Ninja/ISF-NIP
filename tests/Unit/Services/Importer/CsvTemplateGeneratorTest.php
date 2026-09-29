<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\CsvTemplateGenerator;
use App\Services\Importer\ImporterFieldSchema;
use PHPUnit\Framework\TestCase;

class CsvTemplateGeneratorTest extends TestCase
{
    public function test_generates_file_with_utf8_bom(): void
    {
        $gen = new CsvTemplateGenerator();
        $content = $gen->generate();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content, 'template must start with UTF-8 BOM for Excel');
    }

    public function test_header_row_matches_schema(): void
    {
        $gen = new CsvTemplateGenerator();
        $content = $gen->generate();
        // Strip BOM and get first line
        $content = substr($content, 3);
        $firstLine = explode("\n", $content, 2)[0];
        // Parse it back via fgetcsv (in-memory)
        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $firstLine);
        rewind($fh);
        $parsedHeaders = fgetcsv($fh);
        fclose($fh);
        $this->assertSame(ImporterFieldSchema::templateHeaders(), $parsedHeaders);
    }

    public function test_contains_two_sample_rows(): void
    {
        $gen = new CsvTemplateGenerator();
        $content = $gen->generate();
        // Count non-empty lines; should be 3 (1 header + 2 samples)
        $lines = array_values(array_filter(explode("\n", trim($content)), fn($l) => $l !== ''));
        $this->assertCount(3, $lines);
    }

    public function test_sample_row_widths_match_header(): void
    {
        $gen = new CsvTemplateGenerator();
        $content = substr($gen->generate(), 3); // strip BOM
        $lines = array_values(array_filter(explode("\n", trim($content)), fn($l) => $l !== ''));
        $headerWidth = count(str_getcsv($lines[0]));
        $this->assertSame($headerWidth, count(str_getcsv($lines[1])));
        $this->assertSame($headerWidth, count(str_getcsv($lines[2])));
    }
}
