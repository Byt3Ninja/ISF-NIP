<?php

namespace App\Services\Importer;

class RowError
{
    public function __construct(
        public readonly int $row,
        public readonly ?string $fieldKey,
        public readonly string $code,
        public readonly array $params = []
    ) {}

    public function messageEn(): string
    {
        return trans("importer.row_errors.{$this->code}", $this->mergedParams(), 'en');
    }

    public function messageAr(): string
    {
        return trans("importer.row_errors.{$this->code}", $this->mergedParams(), 'ar');
    }

    public function toArray(): array
    {
        return [
            'row'        => $this->row,
            'field_key'  => $this->fieldKey,
            'code'       => $this->code,
            'message_en' => $this->messageEn(),
            'message_ar' => $this->messageAr(),
        ];
    }

    private function mergedParams(): array
    {
        return array_merge(['row' => $this->row], $this->params);
    }
}
