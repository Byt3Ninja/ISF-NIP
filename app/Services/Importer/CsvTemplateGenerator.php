<?php

namespace App\Services\Importer;

class CsvTemplateGenerator
{
    public function generate(): string
    {
        $fp = fopen('php://temp', 'rw+');

        // UTF-8 BOM so Excel opens Arabic columns correctly
        fwrite($fp, "\xEF\xBB\xBF");

        fputcsv($fp, ImporterFieldSchema::templateHeaders());
        foreach (ImporterFieldSchema::templateSampleRows() as $row) {
            fputcsv($fp, $row);
        }

        rewind($fp);
        $content = stream_get_contents($fp);
        fclose($fp);

        return $content;
    }

    public function filename(): string
    {
        return 'startups-import-template.csv';
    }
}
