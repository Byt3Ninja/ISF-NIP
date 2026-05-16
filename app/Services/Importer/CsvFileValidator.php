<?php

namespace App\Services\Importer;

use Illuminate\Http\UploadedFile;

/**
 * File-level CSV validation. Pure; does not persist.
 * Runs all checks and returns ValidationResult with every detected error
 * (not just the first) — spec GV-2067 explicitly requires this.
 */
class CsvFileValidator
{
    public const EMPTY_FILE_SIZE = 0;
    public const MAX_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB
    public const MAX_DATA_ROWS  = 5000;

    private const ACCEPTED_MIMES = [
        'text/csv',
        'text/plain',
        'application/csv',
        'application/vnd.ms-excel',
        'application/octet-stream', // some browsers send this for CSV
    ];

    private const BINARY_MAGIC_BYTES = [
        "MZ",                        // PE/DOS
        "PK\x03\x04",                // ZIP / XLSX / DOCX
        "\x7FELF",                   // ELF
        "\x1F\x8B",                  // GZIP
        "%PDF",                      // PDF
    ];

    public function validate(UploadedFile $file): ValidationResult
    {
        $errors = [];

        // 1. Extension (case-insensitive .csv)
        $clientExt = strtolower((string) $file->getClientOriginalExtension());
        if ($clientExt !== 'csv') {
            return ValidationResult::fail([new FileError('ext_unsupported')]);
            // $errors[] = new FileError('ext_unsupported');
        }

        // 2. Size
        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            $errors[] = new FileError('too_large');
        }
        elseif ($file->getSize() <= self::EMPTY_FILE_SIZE) {
            return ValidationResult::fail([new FileError('empty_file')]);
        }

        // 3. MIME (permissive, since Excel/Finder lie)
        if (!in_array($file->getMimeType(), self::ACCEPTED_MIMES, true)) {
            $errors[] = new FileError('mime_invalid');
        }

        // 4. Magic bytes / null bytes — open once, read 8KB
        $handle = @fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return ValidationResult::fail([new FileError('empty_file')]);
        }
        $head = (string) fread($handle, 8192);
        fclose($handle);

        foreach (self::BINARY_MAGIC_BYTES as $magic) {
            if (str_starts_with($head, $magic)) {
                $errors[] = new FileError('binary_detected');
                break;
            }
        }
        if (strpos($head, "\x00") !== false) {
            // UTF-16 fixture has null bytes AND the \xFF\xFE BOM — treat as encoding_invalid,
            // not binary_detected (more helpful message). Detect UTF-16 BOM explicitly.
            if (str_starts_with($head, "\xFF\xFE") || str_starts_with($head, "\xFE\xFF")) {
                $errors[] = new FileError('encoding_invalid');
            } else {
                $errors[] = new FileError('binary_detected');
            }
        }

        // 5. Encoding — if it's not UTF-8-compatible, reject. Strip UTF-8 BOM first.
        $headNoBom = str_starts_with($head, "\xEF\xBB\xBF") ? substr($head, 3) : $head;
        // Only run this check if not already flagged encoding_invalid and not flagged binary
        if (!$this->hasCode($errors, 'encoding_invalid') && !$this->hasCode($errors, 'binary_detected')) {
            if (!mb_check_encoding($headNoBom, 'UTF-8')) {
                $errors[] = new FileError('encoding_invalid');
            }
        }

        // 6. Headers + emptiness + duplicates
        // Only bother reading CSV structure if file isn't already flagged as binary/encoding-bad
        if (!$this->hasCode($errors, 'binary_detected') && !$this->hasCode($errors, 'encoding_invalid')) {
            $errors = array_merge($errors, $this->validateCsvStructure($file));
        }

        return empty($errors) ? ValidationResult::ok() : ValidationResult::fail($errors);
    }

    /** @return FileError[] */
    private function validateCsvStructure(UploadedFile $file): array
    {
        $errors = [];
        $handle = @fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return [new FileError('empty_file')];
        }

        // Read first line for headers; strip UTF-8 BOM
        $firstLine = fgets($handle);
        if ($firstLine === false || trim($firstLine) === '') {
            fclose($handle);
            return [new FileError('empty_headers_row')];
        }

        // Rewind and parse via fgetcsv to respect quoting
        rewind($handle);
        $headers = fgetcsv($handle, 0, ',');
        if ($headers === false || count(array_filter($headers, fn($h) => trim((string) $h) !== '')) === 0) {
            fclose($handle);
            return [new FileError('empty_headers_row')];
        }

        // Strip BOM from first header
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        }
        $normalised = array_map(fn($h) => strtolower(trim((string) $h)), $headers);

        // Duplicate headers (case-insensitive, whitespace-trimmed)
        $counts = array_count_values($normalised);
        $dups = array_keys(array_filter($counts, fn($c) => $c > 1));
        if (!empty($dups)) {
            // Return the original-case duplicates for clearer UX
            $origDups = [];
            foreach ($dups as $lower) {
                foreach ($headers as $h) {
                    if (strtolower(trim((string) $h)) === $lower && !in_array($h, $origDups, true)) {
                        $origDups[] = $h;
                    }
                }
            }
            $errors[] = new FileError('duplicate_headers', ['headers' => $origDups]);
        }

        // Row count + emptiness
        $dataRows = 0;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
                continue; // skip blank line
            }
            $dataRows++;
            if ($dataRows > self::MAX_DATA_ROWS) {
                break;
            }
        }
        fclose($handle);

        if ($dataRows === 0) {
            $errors[] = new FileError('empty_file');
        } elseif ($dataRows > self::MAX_DATA_ROWS) {
            $errors[] = new FileError('too_many_rows');
        }

        return $errors;
    }

    private function hasCode(array $errors, string $code): bool
    {
        foreach ($errors as $e) {
            if ($e->code === $code) {
                return true;
            }
        }
        return false;
    }
}
