<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\CsvParser;
use PHPUnit\Framework\TestCase;

class CsvParserTest extends TestCase
{
    private CsvParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new CsvParser();
    }

    private function fixture(string $name): string
    {
        return __DIR__ . '/../../../Fixtures/Importer/' . $name;
    }

    public function test_parses_minimal_file(): void
    {
        $result = $this->parser->parse($this->fixture('valid_minimal.csv'));
        $this->assertCount(12, $result['headers']);
        $this->assertSame('Program Name', $result['headers'][0]);
        $this->assertCount(1, $result['rows']);
        $this->assertSame(1, $result['row_count']);
    }

    public function test_strips_utf8_bom_from_first_header(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'bom_');
        file_put_contents($tmp, "\xEF\xBB\xBFA,B,C\n1,2,3\n");
        $result = $this->parser->parse($tmp);
        unlink($tmp);
        $this->assertSame('A', $result['headers'][0], 'BOM should be stripped');
    }

    public function test_handles_quoted_fields_with_commas(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'quoted_');
        file_put_contents($tmp, "Name,Markets\nAcme,\"Egypt, Saudi Arabia\"\n");
        $result = $this->parser->parse($tmp);
        unlink($tmp);
        $this->assertSame('Egypt, Saudi Arabia', $result['rows'][0][1]);
    }

    public function test_pads_short_rows_to_header_width(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'short_');
        file_put_contents($tmp, "A,B,C\n1,2\n");
        $result = $this->parser->parse($tmp);
        unlink($tmp);
        $this->assertCount(3, $result['rows'][0], 'row should be padded to 3 columns');
        $this->assertSame('', $result['rows'][0][2]);
    }

    public function test_truncates_long_rows_to_header_width(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'long_');
        file_put_contents($tmp, "A,B\n1,2,3,4\n");
        $result = $this->parser->parse($tmp);
        unlink($tmp);
        $this->assertCount(2, $result['rows'][0]);
    }

    public function test_skips_entirely_blank_lines(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'blank_');
        file_put_contents($tmp, "A,B\n1,2\n\n\n3,4\n");
        $result = $this->parser->parse($tmp);
        unlink($tmp);
        $this->assertSame(2, $result['row_count']);
    }

    public function test_normalises_whitespace_in_headers(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'ws_');
        file_put_contents($tmp, "  Program Name  ,\tFounder Email\t\n1,a@b.com\n");
        $result = $this->parser->parse($tmp);
        unlink($tmp);
        $this->assertSame('Program Name', $result['headers'][0]);
        $this->assertSame('Founder Email', $result['headers'][1]);
    }
}
