<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Company;
use App\User;
use App\Country;
use App\State;
use App\City;
use App\Stage;
use App\Sector;
use App\Milestone;
use App\Team_member;

class CompanyModelTest extends TestCase
{

    /** @test */
    public function it_has_fillable_attributes()
    {
        $company = new Company();
        $fillable = $company->getFillable();

        $this->assertContains('name_en', $fillable);
        $this->assertContains('name_ar', $fillable);
        $this->assertContains('email', $fillable);
    }

    /** @test */
    public function it_can_belong_to_user()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $company->user);
        $this->assertEquals($user->id, $company->user->id);
    }

    /** @test */
    public function it_can_belong_to_country()
    {
        $country = Country::factory()->create();
        $company = Company::factory()->create(['country_id' => $country->id]);

        $this->assertInstanceOf(Country::class, $company->country);
        $this->assertEquals($country->id, $company->country->id);
    }

    /** @test */
    public function it_can_belong_to_state()
    {
        $state = State::factory()->create();
        $company = Company::factory()->create(['state_id' => $state->id]);

        $this->assertInstanceOf(State::class, $company->state);
        $this->assertEquals($state->id, $company->state->id);
    }

    /** @test */
    public function it_can_belong_to_city()
    {
        $city = City::factory()->create();
        $company = Company::factory()->create(['city_id' => $city->id]);

        $this->assertInstanceOf(City::class, $company->city);
        $this->assertEquals($city->id, $company->city->id);
    }

    /** @test */
    public function it_can_belong_to_stage()
    {
        $stage = Stage::factory()->create();
        $company = Company::factory()->create(['stage' => $stage->id]);

        $this->assertInstanceOf(Stage::class, $company->stagE);
        $this->assertEquals($stage->id, $company->stagE->id);
    }

    /** @test */
    public function it_can_belong_to_sector()
    {
        $sector = Sector::factory()->create();
        $company = Company::factory()->create(['sector' => $sector->id]);

        $this->assertInstanceOf(Sector::class, $company->sectoR);
        $this->assertEquals($sector->id, $company->sectoR->id);
    }

    /** @test */
    public function it_can_have_milestones()
    {
        $company = Company::factory()->create();
        Milestone::factory()->count(3)->create(['Company_id' => $company->id]);

        $this->assertCount(3, $company->milestones);
    }

    /** @test */
    public function it_can_have_team_members()
    {
        $company = Company::factory()->create();
        Team_member::factory()->count(5)->create(['Company_id' => $company->id]);

        $this->assertCount(5, $company->teamMembers);
    }

    /** @test */
    public function it_has_name_attribute_accessor()
    {
        app()->setLocale('en');
        $company = Company::factory()->create([
            'name_en' => 'Test Company',
            'name_ar' => 'شركة تجريبية',
        ]);

        $this->assertEquals('Test Company', $company->name);
    }

    /** @test */
    public function it_has_name_attribute_in_arabic()
    {
        app()->setLocale('ar');
        $company = Company::factory()->create([
            'name_en' => 'Test Company',
            'name_ar' => 'شركة تجريبية',
        ]);

        $this->assertEquals('شركة تجريبية', $company->name);
    }

    /** @test */
    public function it_has_logo_url_attribute()
    {
        $company = Company::factory()->create(['logo' => 'test-logo.jpg']);

        $this->assertStringContainsString('test-logo.jpg', $company->logo_url);
    }

    /** @test */
    public function it_returns_placeholder_when_no_logo()
    {
        $company = Company::factory()->create(['logo' => null]);

        $this->assertStringContainsString('placeholder', $company->logo_url);
    }

    /** @test */
    public function it_casts_business_categories_to_array()
    {
        $categories = ['tech', 'finance', 'health'];
        $company = Company::factory()->create([
            'business_categories' => json_encode($categories),
        ]);

        $this->assertIsArray($company->business_categories);
        $this->assertEquals($categories, $company->business_categories);
    }

    /** @test */
    public function it_casts_product_photo_to_array()
    {
        $photos = ['photo1.jpg', 'photo2.jpg'];
        $company = Company::factory()->create([
            'product_photo' => json_encode($photos),
        ]);

        $this->assertIsArray($company->product_photo);
        $this->assertEquals($photos, $company->product_photo);
    }

    /** @test */
    public function it_can_have_funding_rounds()
    {
        $company = Company::factory()->create();
        $company->fundingRounds()->create([
            'round_type' => 'Seed',
            'amount' => 100000,
            'date' => now(),
        ]);

        $this->assertCount(1, $company->fundingRounds);
    }

    /** @test */
    public function it_can_have_employees()
    {
        $company = Company::factory()->create();
        $company->employees()->create([
            'name' => 'John Employee',
            'position' => 'Developer',
        ]);

        $this->assertCount(1, $company->employees);
    }
}

