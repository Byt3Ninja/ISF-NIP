<?php

namespace App\Services\Importer;

use Illuminate\Support\Facades\App;

/**
 * Immutable file-level validation error.
 * Resolves EN + AR messages via the importer lang namespace on demand.
 */
class FileError
{
    public function __construct(
        public readonly string $code,
        public readonly array $params = []
    ) {}

    public function messageEn(): string
    {
        return trans("importer.errors.{$this->code}", $this->formattedParams(), 'en');
    }

    public function messageAr(): string
    {
        return trans("importer.errors.{$this->code}", $this->formattedParams(), 'ar');
    }

    public function toArray(): array
    {
        return [
            'code'       => $this->code,
            'message_en' => $this->messageEn(),
            'message_ar' => $this->messageAr(),
            'params'     => $this->params,
        ];
    }

    private function formattedParams(): array
    {
        $out = [];
        foreach ($this->params as $k => $v) {
            $out[$k] = is_array($v) ? implode(', ', $v) : $v;
        }
        return $out;
    }
}
