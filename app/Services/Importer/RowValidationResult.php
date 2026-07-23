<?php

namespace App\Services\Importer;

class RowValidationResult
{
    /**
     * @param RowError[] $errors
     * @param RowError[] $warnings
     */
    public function __construct(
        public readonly array $errors,
        public readonly array $warnings,
        public readonly int $totalRows
    ) {}

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function toResponseArray(): array
    {
        return [
            'valid'        => $this->isValid(),
            'row_errors'   => array_map(fn(RowError $e) => $e->toArray(), $this->errors),
            'row_warnings' => array_map(fn(RowError $w) => $w->toArray(), $this->warnings),
            'totals' => [
                'rows'     => $this->totalRows,
                'blocking' => count($this->errors),
                'warning'  => count($this->warnings),
            ],
        ];
    }
}
