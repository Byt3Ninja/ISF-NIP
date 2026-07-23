<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UnauthenticatedApiTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function unauthenticated_post_expertise_returns_401()
    {
        $response = $this->postJson('/api/expertise', [
            'name' => 'Test Expertise',
        ]);

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function unauthenticated_put_expertise_returns_401()
    {
        $response = $this->putJson('/api/expertise/1', [
            'name' => 'Updated Expertise',
        ]);

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function unauthenticated_delete_expertise_returns_401()
    {
        $response = $this->deleteJson('/api/expertise/1');

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function unauthenticated_post_specializations_returns_401()
    {
        $response = $this->postJson('/api/specializations', [
            'name' => 'Test Specialization',
        ]);

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function unauthenticated_delete_specializations_returns_401()
    {
        $response = $this->deleteJson('/api/specializations/1');

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function unauthenticated_import_expertise_returns_401()
    {
        $response = $this->postJson('/api/expertise/import', []);

        $this->assertContains($response->getStatusCode(), [401, 403]);
    }

    /** @test */
    public function public_get_expertise_still_accessible()
    {
        $response = $this->getJson('/api/expertise');

        // GET should still be public (200 or empty result, but NOT 401)
        $this->assertNotEquals(401, $response->getStatusCode());
    }

    /** @test */
    public function public_get_specializations_still_accessible()
    {
        $response = $this->getJson('/api/specializations');

        $this->assertNotEquals(401, $response->getStatusCode());
    }
}
