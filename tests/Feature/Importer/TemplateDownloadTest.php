<?php

namespace Tests\Feature\Importer;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TemplateDownloadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_returns_csv_with_utf8_bom_and_attachment_header(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');

        $resp = $this->actingAs($user)->get('/dashboard/importer/template');

        $resp->assertStatus(200);
        $this->assertSame('text/csv; charset=UTF-8', $resp->headers->get('content-type'));
        $this->assertStringContainsString('startups-import-template.csv', (string) $resp->headers->get('content-disposition'));
        $content = $resp->getContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content, 'must start with UTF-8 BOM');
        // must contain at least one known header
        $this->assertStringContainsString('Program Name', $content);
    }
}
