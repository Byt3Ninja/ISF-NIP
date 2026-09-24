<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\User;
use App\Company;
use App\Role;
use App\Organization;
use Illuminate\Support\Facades\Hash;

class UserModelTest extends TestCase
{

    /** @test */
    public function it_has_fillable_attributes()
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('first_name_en', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('phone', $fillable);
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /** @test */
    public function it_can_belong_to_company()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        
        // Refresh to ensure relationship is loaded
        $user->refresh();

        $this->assertInstanceOf(Company::class, $user->company);
        $this->assertEquals($company->id, $user->company->id);
    }

    /** @test */
    public function it_can_belong_to_organization()
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();
        $user->organization_id = $organization->id;
        $user->save();
        
        // Refresh to ensure relationship is loaded
        $user->refresh();

        $this->assertInstanceOf(Organization::class, $user->organization);
        $this->assertEquals($organization->id, $user->organization->id);
    }

    /** @test */
    public function it_can_have_roles()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Mentor']);
        $user->assignRole('Mentor');

        $this->assertTrue($user->hasRole('Mentor'));
        $this->assertCount(1, $user->roles);
    }

    /** @test */
    public function it_can_check_permissions()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Admin']);
        $user->assignRole('Admin');
        
        // Assuming permission is assigned to role
        $role->givePermissionTo('users.index');

        $this->assertTrue($user->hasPermissionTo('users.index'));
    }

    /** @test */
    public function it_has_name_attribute_accessor()
    {
        app()->setLocale('en');
        $user = User::factory()->create([
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
        ]);
        
        // Refresh to ensure attributes are loaded
        $user->refresh();

        $this->assertEquals('John Doe', $user->name);
    }

    /** @test */
    public function it_has_name_attribute_in_arabic()
    {
        app()->setLocale('ar');
        $user = User::factory()->create([
            'first_name_ar' => 'جون',
            'last_name_ar' => 'دو',
        ]);
        
        // Refresh to ensure attributes are loaded
        $user->refresh();

        $this->assertEquals('جون دو', $user->name);
    }

    /** @test */
    public function it_has_image_url_attribute()
    {
        $user = User::factory()->create(['img_url' => 'test.jpg']);
        
        // Refresh to ensure attributes are loaded
        $user->refresh();

        $this->assertStringContainsString('test.jpg', $user->image_url);
    }

    /** @test */
    public function it_returns_placeholder_when_no_image()
    {
        $user = User::factory()->create(['img_url' => null]);
        
        // Refresh to ensure attributes are loaded
        $user->refresh();

        $this->assertStringContainsString('placeholder', $user->image_url);
    }

    /** @test */
    public function it_can_check_if_user_has_completed_profile()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'country_id' => 1,
            'address' => 'Test Address',
            'timezone' => 'UTC',
        ]);
        
        // Refresh to ensure attributes are loaded
        $user->refresh();

        $this->assertTrue($user->hasCompletedProfile());
    }

    /** @test */
    public function it_returns_false_when_profile_incomplete()
    {
        $user = User::factory()->create([
            'phone' => null,
            'country_id' => null,
            'address' => null,
            'timezone' => null,
        ]);
        
        // Refresh to ensure attributes are loaded
        $user->refresh();

        $this->assertFalse($user->hasCompletedProfile());
    }

    /** @test */
    public function it_can_check_if_user_has_completed_startup()
    {
        $company = Company::factory()->create([
            'name_en' => 'Test Company',
            'address_en' => 'Test Address',
            'est_date' => now(),
            'stage' => 1,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);
        
        // Refresh to ensure relationship is loaded
        $user->refresh();

        $this->assertTrue($user->hasCompletedStartup());
    }

    /** @test */
    public function it_returns_false_when_startup_incomplete()
    {
        $company = Company::factory()->create([
            'name_en' => null,
            'address_en' => null,
            'est_date' => null,
            'stage' => null,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);
        
        // Refresh to ensure relationship is loaded
        $user->refresh();

        $this->assertFalse($user->hasCompletedStartup());
    }

    /** @test */
    public function it_can_get_my_permissions()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Admin']);
        $user->assignRole('Admin');
        $role->givePermissionTo('users.index');
        $role->givePermissionTo('users.create');
        
        // Refresh to ensure relationships are loaded
        $user->refresh();

        $permissions = $user->myPermissions();

        $this->assertIsArray($permissions);
        $this->assertContains('users.index', $permissions);
        $this->assertContains('users.create', $permissions);
    }

    /** @test */
    public function it_can_check_able_to_permission()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Admin']);
        $user->assignRole('Admin');
        $role->givePermissionTo('users.index');

        $this->assertTrue($user->ableTo('users.index'));
        $this->assertFalse($user->ableTo('users.delete'));
    }

    /** @test */
    public function it_can_check_able_to_any_permission()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Admin']);
        $user->assignRole('Admin');
        $role->givePermissionTo('users.index');

        $this->assertTrue($user->ableToAny(['users.index', 'users.delete']));
        $this->assertFalse($user->ableToAny(['users.delete', 'users.destroy']));
    }

    /** @test */
    public function it_can_check_able_to_all_permissions()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'Admin']);
        $user->assignRole('Admin');
        $role->givePermissionTo('users.index');
        $role->givePermissionTo('users.create');

        $this->assertTrue($user->ableToAll(['users.index', 'users.create']));
        $this->assertFalse($user->ableToAll(['users.index', 'users.delete']));
    }

    /** @test */
    public function it_casts_email_verified_at_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }

    /** @test */
    public function it_casts_boolean_attributes()
    {
        $user = User::factory()->create([
            'accept_email' => 1,
            'accept_whatsapp' => 0,
        ]);

        $this->assertIsBool($user->accept_email);
        $this->assertTrue($user->accept_email);
        $this->assertFalse($user->accept_whatsapp);
    }
}

