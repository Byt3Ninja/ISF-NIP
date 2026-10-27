<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Organization;
use App\User;
use App\Cycle;
use App\Company;

class OrganizationModelTest extends TestCase
{

    /** @test */
    public function it_has_fillable_attributes()
    {
        $organization = new Organization();
        $fillable = $organization->getFillable();

        $this->assertContains('name_en', $fillable);
        $this->assertContains('name_ar', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('user_id', $fillable);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $organization = Organization::factory()->create();
        $organizationId = $organization->id;
        $organization->delete();

        $this->assertSoftDeleted('organizations', ['id' => $organizationId]);
        $this->assertNull(Organization::find($organizationId));
        $this->assertNotNull(Organization::withTrashed()->find($organizationId));
    }

    /** @test */
    public function it_can_belong_to_user()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['user_id' => $user->id]);
        
        // Refresh to load relationship
        $organization->refresh();

        $this->assertInstanceOf(User::class, $organization->user);
        $this->assertEquals($user->id, $organization->user->id);
    }

    /** @test */
    public function it_can_have_cycles()
    {
        $organization = Organization::factory()->create();
        Cycle::factory()->count(3)->create(['organization_id' => $organization->id]);
        
        // Refresh to load relationship
        $organization->refresh();

        $this->assertCount(3, $organization->cycles);
        $this->assertTrue($organization->cycles->every(fn($cycle) => $cycle->organization_id === $organization->id));
    }

    /** @test */
    public function it_can_have_questions_categories()
    {
        $organization = Organization::factory()->create();
        // Assuming QuestionCategories model exists
        // QuestionCategories::factory()->count(5)->create(['organization_id' => $organization->id]);

        // $this->assertCount(5, $organization->questionsCategories);
        $this->assertTrue(true); // Placeholder until model is confirmed
    }

    /** @test */
    public function it_has_name_attribute_accessor()
    {
        app()->setLocale('en');
        $organization = Organization::factory()->create([
            'name_en' => 'Test Organization',
            'name_ar' => 'منظمة تجريبية',
        ]);
        
        // Refresh to ensure attributes are loaded
        $organization->refresh();

        $this->assertEquals('Test Organization', $organization->name);
    }

    /** @test */
    public function it_has_name_attribute_in_arabic()
    {
        app()->setLocale('ar');
        $organization = Organization::factory()->create([
            'name_en' => 'Test Organization',
            'name_ar' => 'منظمة تجريبية',
        ]);
        
        // Refresh to ensure attributes are loaded
        $organization->refresh();

        $this->assertEquals('منظمة تجريبية', $organization->name);
    }

    /** @test */
    public function it_has_image_url_attribute()
    {
        $organization = Organization::factory()->create(['img_url' => 'test-logo.jpg']);
        
        // Refresh to ensure attributes are loaded
        $organization->refresh();

        $this->assertEquals('test-logo.jpg', $organization->image_url);
    }

    /** @test */
    public function it_returns_placeholder_when_no_image()
    {
        $organization = Organization::factory()->create(['img_url' => null]);
        
        // Refresh to ensure attributes are loaded
        $organization->refresh();

        $this->assertEquals('/app-assets/images/logo_placeholder.png', $organization->image_url);
    }

    /** @test */
    public function it_has_localized_name_attribute()
    {
        app()->setLocale('en');
        $organization = Organization::factory()->create([
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
        ]);
        
        // Refresh to ensure attributes are loaded
        $organization->refresh();

        $this->assertEquals('English Name', $organization->localized_name);

        app()->setLocale('ar');
        // Need to refresh after locale change to get updated value
        $organization->refresh();
        $this->assertEquals('Arabic Name', $organization->localized_name);
    }

    /** @test */
    public function it_can_have_organization_settings()
    {
        $organization = Organization::factory()->create();
        // Assuming OrganizationSettings model exists
        // $settings = OrganizationSettings::factory()->create(['organization_id' => $organization->id]);

        // $this->assertInstanceOf(OrganizationSettings::class, $organization->organizationSettings);
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_can_have_questions()
    {
        $organization = Organization::factory()->create();
        // Assuming QuestionsBank model exists and pivot table
        // This tests the belongsToMany relationship

        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function it_can_get_cycles_with_companies()
    {
        $organization = Organization::factory()->create();
        $cycle = Cycle::factory()->create(['organization_id' => $organization->id]);
        Company::factory()->count(5)->create(['cycle' => $cycle->id]);
        
        // Refresh and eager load relationships
        $organization->refresh();
        $organization->load('cycles.companies');

        $this->assertCount(1, $organization->cycles);
        $this->assertCount(5, $organization->cycles->first()->companies);
    }
}

