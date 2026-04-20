<?php

namespace App\Services\Importer;

class ValidationResult
{
    /** @param FileError[] $errors */
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors = []
    ) {}

    public static function ok(): self
    {
        return new self(true, []);
    }

    public static function fail(array $errors): self
    {
        return new self(false, $errors);
    }

    public function withError(FileError $error): self
    {
        return new self(false, [...$this->errors, $error]);
    }

    public function mergedWith(ValidationResult $other): self
    {
        return new self(
            $this->valid && $other->valid,
            [...$this->errors, ...$other->errors]
        );
    }

    public function toResponseArray(): array
    {
        return array_map(fn(FileError $e) => $e->toArray(), $this->errors);
    }
}
