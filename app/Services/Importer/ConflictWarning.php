<?php

namespace App\Services\Importer;

class ConflictWarning
{
    public function __construct(
        public readonly int $row,
        public readonly string $type,
        public readonly ?string $fieldKey = null,
        public readonly ?string $value = null,
        public readonly array $extras = []
    ) {}

    public function messageEn(): string
    {
        return trans("importer.warnings.{$this->type}", ['row' => $this->row], 'en');
    }

    public function messageAr(): string
    {
        return trans("importer.warnings.{$this->type}", ['row' => $this->row], 'ar');
    }

    public function toArray(): array
    {
        return [
            'row'        => $this->row,
            'type'       => $this->type,
            'field_key'  => $this->fieldKey,
            'value'      => $this->value,
            'extras'     => $this->extras,
            'message_en' => $this->messageEn(),
            'message_ar' => $this->messageAr(),
        ];
    }
}
