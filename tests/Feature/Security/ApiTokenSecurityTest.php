<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\User;
use App\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenSecurityTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function sanctum_token_has_correct_format()
    {
        $user = User::factory()->create();

        $plainToken = $user->createToken('registration')->plainTextToken;

        $this->assertNotEmpty($plainToken);
        $this->assertStringContainsString('|', $plainToken, 'Token should be in Sanctum format (id|hash)');
    }

    /** @test */
    public function sanctum_token_is_hashed_in_database()
    {
        $user = User::factory()->create();

        $plainToken = $user->createToken('registration')->plainTextToken;

        // Extract the plain part and find the matching token by its hash
        $plainPart = explode('|', $plainToken)[1];
        $expectedHash = hash('sha256', $plainPart);

        $tokenRecord = PersonalAccessToken::where('tokenable_id', $user->id)
            ->where('tokenable_type', get_class($user))
            ->latest()
            ->first();

        $this->assertNotNull($tokenRecord);
        // The stored token should be a SHA-256 hash, not the plain token
        $this->assertNotEquals($plainToken, $tokenRecord->token, 'Token should be hashed in DB');
        // The plain token part should NOT match the stored hash
        $this->assertNotEquals($plainPart, $tokenRecord->token, 'Plain token part should not match stored hash');
        // Verify the hash matches
        $this->assertEquals($expectedHash, $tokenRecord->token, 'Stored token should be SHA-256 hash');
    }

    /** @test */
    public function old_plaintext_api_token_lookup_does_not_work()
    {
        $user = User::factory()->create();
        $user->api_token = 'old_plaintext_token_12345';
        $user->save();

        $response = $this->getJson('/api/company?request_token=old_plaintext_token_12345');

        $response->assertStatus(404);
    }

    /** @test */
    public function sanctum_token_works_with_get_company()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create([
            'business_categories' => ['Technology'],
            'product_photo' => [],
        ]);
        $user->company_id = $company->id;
        $user->HaveCompany = true;
        $user->save();

        $plainToken = $user->createToken('registration')->plainTextToken;

        $companyResponse = $this->getJson('/api/company?request_token=' . $plainToken);

        $companyResponse->assertStatus(200);
        $companyResponse->assertJsonPath('data.company.business_or_product_name', $company->name_en);
    }

    /** @test */
    public function api_token_not_stored_in_users_table()
    {
        $user = User::factory()->create();
        $user->createToken('registration')->plainTextToken;

        // Refresh from DB
        $user->refresh();

        // api_token column should be null (not used for new registrations)
        $this->assertNull($user->api_token, 'api_token should not be set when using Sanctum');
    }
}
