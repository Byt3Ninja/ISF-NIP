<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\CsvFileValidator;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class CsvFileValidatorTest extends TestCase
{
    private CsvFileValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CsvFileValidator();
    }

    private function fixturePath(string $name): string
    {
        return __DIR__ . '/../../../Fixtures/Importer/' . $name;
    }

    private function fake(string $fixture, string $mime = 'text/csv', ?string $clientName = null): UploadedFile
    {
        $path = $this->fixturePath($fixture);
        // test: false means "do not move the file" — UploadedFile references the real path
        return new UploadedFile($path, $clientName ?? $fixture, $mime, null, true);
    }

    public function test_valid_minimal_passes(): void
    {
        $result = $this->validator->validate($this->fake('valid_minimal.csv'));
        $this->assertTrue($result->valid, print_r(array_map(fn($e) => $e->code, $result->errors), true));
    }

    public function test_rejects_non_csv_extension(): void
    {
        $file = $this->fake('valid_minimal.csv', 'text/csv', 'file.xlsx');
        $result = $this->validator->validate($file);
        $this->assertFalse($result->valid);
        $this->assertContains('ext_unsupported', array_map(fn($e) => $e->code, $result->errors));
    }

    public function test_rejects_binary_by_magic_bytes(): void
    {
        $file = $this->fake('invalid_binary.bin', 'application/octet-stream', 'fakecsv.csv');
        $result = $this->validator->validate($file);
        $this->assertFalse($result->valid);
        $this->assertContains('binary_detected', array_map(fn($e) => $e->code, $result->errors));
    }

    public function test_rejects_empty_file(): void
    {
        $result = $this->validator->validate($this->fake('invalid_empty.csv'));
        $this->assertFalse($result->valid);
        $this->assertContains('empty_file', array_map(fn($e) => $e->code, $result->errors));
    }

    public function test_rejects_empty_headers_row(): void
    {
        $result = $this->validator->validate($this->fake('empty_headers.csv'));
        $this->assertFalse($result->valid);
        $this->assertContains('empty_headers_row', array_map(fn($e) => $e->code, $result->errors));
    }

    public function test_rejects_duplicate_headers(): void
    {
        $result = $this->validator->validate($this->fake('invalid_duplicate_headers.csv'));
        $this->assertFalse($result->valid);
        $this->assertContains('duplicate_headers', array_map(fn($e) => $e->code, $result->errors));
    }

    public function test_rejects_too_many_rows(): void
    {
        $result = $this->validator->validate($this->fake('invalid_too_large.csv'));
        $this->assertFalse($result->valid);
        $this->assertContains('too_many_rows', array_map(fn($e) => $e->code, $result->errors));
    }

    public function test_rejects_non_utf8_encoding(): void
    {
        $result = $this->validator->validate($this->fake('invalid_encoding.csv'));
        $this->assertFalse($result->valid);
        $this->assertContains('encoding_invalid', array_map(fn($e) => $e->code, $result->errors));
    }

    public function test_accepts_utf8_with_arabic_content(): void
    {
        $result = $this->validator->validate($this->fake('unicode_arabic.csv'));
        $this->assertTrue($result->valid, 'Arabic UTF-8 content should pass');
    }

    public function test_returns_all_errors_not_just_first(): void
    {
        // Duplicate-headers file with a bad extension on top
        $file = $this->fake('invalid_duplicate_headers.csv', 'text/csv', 'file.exe');
        $result = $this->validator->validate($file);
        $codes = array_map(fn($e) => $e->code, $result->errors);
        $this->assertContains('ext_unsupported', $codes);
        $this->assertContains('duplicate_headers', $codes);
    }
}
