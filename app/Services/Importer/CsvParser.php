<?php

namespace App\Services\Importer;

/**
 * Streaming CSV parser.
 *
 * Returns:
 *   [
 *     'headers'   => string[],
 *     'rows'      => array<int, string[]>,  // positional, padded/truncated to header width
 *     'row_count' => int,
 *   ]
 */
class CsvParser
{
    public function parse(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open CSV at {$path}");
        }

        $headers = fgetcsv($handle, 0, ',');
        if ($headers === false) {
            fclose($handle);
            return ['headers' => [], 'rows' => [], 'row_count' => 0];
        }

        // Strip UTF-8 BOM on the first header
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        }

        // Normalise whitespace in every header
        $headers = array_map(fn($h) => preg_replace('/\s+/u', ' ', trim((string) $h)), $headers);
        $width = count($headers);

        $rows = [];
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            // Skip entirely blank lines
            if ($row === [null] || count(array_filter($row, fn($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            // Pad or truncate to header width
            if (count($row) < $width) {
                $row = array_pad($row, $width, '');
            } elseif (count($row) > $width) {
                $row = array_slice($row, 0, $width);
            }

            // Coerce all values to strings (fgetcsv may return null for trailing empty)
            $row = array_map(fn($v) => (string) ($v ?? ''), $row);

            $rows[] = $row;
        }

        fclose($handle);

        return [
            'headers'   => $headers,
            'rows'      => $rows,
            'row_count' => count($rows),
        ];
    }
}
