<?php

namespace Tests\Unit\Services\Importer;

use App\Services\Importer\ConflictWarning;
use Tests\TestCase;

class ConflictWarningTest extends TestCase
{
    public function test_to_array_includes_all_fields_and_both_locales(): void
    {
        $w = new ConflictWarning(
            row: 4,
            type: 'founder_email_already_enrolled',
            fieldKey: 'founder_email',
            value: 'x@y.com',
            extras: ['existing_user_id' => 7]
        );
        $arr = $w->toArray();
        $this->assertSame(4, $arr['row']);
        $this->assertSame('founder_email_already_enrolled', $arr['type']);
        $this->assertSame('founder_email', $arr['field_key']);
        $this->assertSame('x@y.com', $arr['value']);
        $this->assertSame(7, $arr['extras']['existing_user_id']);
        $this->assertIsString($arr['message_en']);
        $this->assertIsString($arr['message_ar']);
        $this->assertStringContainsString('Row 4', $arr['message_en']);
    }
}
