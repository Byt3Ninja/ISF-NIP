<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\ImporterFieldSchema;
use PHPUnit\Framework\TestCase;

class ImporterFieldSchemaTest extends TestCase
{
    public function test_public_api_methods_exist(): void
    {
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'all'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'required'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'bySection'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'find'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'allAliases'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'crossFieldRules'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'templateHeaders'));
        $this->assertTrue(method_exists(ImporterFieldSchema::class, 'templateSampleRows'));
    }
}
