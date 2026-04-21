<?php

namespace Tests\Feature\Importer;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SchemaEndpointTest extends TestCase
{
    use DatabaseTransactions;

    public function test_schema_endpoint_returns_structured_fields(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');

        $resp = $this->actingAs($user)->getJson('/dashboard/importer/schema');

        $resp->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.total_fields', 58)
             ->assertJsonStructure([
                 'data' => [
                     'sections',
                     'fields' => ['program_name' => ['label_en', 'label_ar', 'section', 'required']],
                     'cross_field_rules',
                 ],
             ]);
    }
}
