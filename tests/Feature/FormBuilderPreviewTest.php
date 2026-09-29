<?php

namespace Tests\Feature;

use App\User;
use App\Organization;
use App\BuilderForm;
use Database\Seeders\FieldSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FormBuilderPreviewTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FieldSeeder::class);
        $this->withoutMiddleware(\App\Http\Middleware\LoginSecurityMiddleware::class);

        \DB::table('settings')->insertOrIgnore([
            'id' => 1,
            'type' => '1',
            'title' => 'Test Platform',
            'ar_title' => 'منصة اختبار',
            'assessment' => 1,
        ]);

        $role = \App\Role::where('default_for', 'admin')->first();
        if (!$role) {
            $role = \App\Role::create([
                'name' => 'Admin',
                'default_for' => 'admin',
            ]);
        }
        $p1 = \App\Permission::firstOrCreate([
            'name' => 'forms.design_save',
        ], [
            'description' => 'Save Form',
            'is_core' => false,
        ]);
        $p2 = \App\Permission::firstOrCreate([
            'name' => 'forms.builder_view',
        ], [
            'description' => 'Builder View',
            'is_core' => false,
        ]);
        $p3 = \App\Permission::firstOrCreate([
            'name' => 'forms.submissions_create',
        ], [
            'description' => 'Submit Form',
            'is_core' => false,
        ]);
        $p4 = \App\Permission::firstOrCreate([
            'name' => 'forms.render_view',
        ], [
            'description' => 'Render View',
            'is_core' => false,
        ]);
        $role->permissions()->syncWithoutDetaching([$p1->id, $p2->id, $p3->id, $p4->id]);
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_preview_form_localization_in_arabic(): void
    {
        $user = User::factory()->create();
        $user->language = 'ar';
        $user->save();
        $user->allotRole('Admin');

        $org = Organization::create([
            'name_en' => 'Test Org EN',
            'name_ar' => 'Test Org AR',
            'user_id' => $user->id,
        ]);

        $form = BuilderForm::create([
            'form_name' => 'Test Preview Form',
            'organization_id' => $org->id,
        ]);

        $field1 = \App\Field::create([
            'name_en' => 'url_question',
            'name_ar' => 'url_question',
            'field_type' => 'url',
        ]);

        $form->fields()->attach($field1->id, [
            'options' => json_encode([
                'label' => 'Website Link',
                'description' => 'Enter your website link',
                'section' => 'Section A',
                'type' => 'url',
                'validation' => ['required' => 1],
            ]),
            'sort_order' => 1,
        ]);

        $field2 = \App\Field::create([
            'name_en' => 'number_question',
            'name_ar' => 'number_question',
            'field_type' => 'number',
        ]);

        $form->fields()->attach($field2->id, [
            'options' => json_encode([
                'label' => 'Phone Number',
                'description' => 'Enter your phone number',
                'section' => 'Section A',
                'type' => 'number',
                'validation' => ['required' => 0],
            ]),
            'sort_order' => 2,
        ]);

        // Get request in Arabic locale
        $response = $this->actingAs($user)
            ->withSession(['locale' => 'ar'])
            ->getJson("/dashboard/show-form/{$form->id}?locale=ar");

        $response->assertStatus(200);

        // Assert mandatory/optional field indicator text is correct
        $response->assertSee('حقل إلزامي');
        $response->assertSee('حقل اختياري');

        // Assert question type labels are translated
        $response->assertSee('رابط'); 
        $response->assertSee('رقم'); 

        // Assert next button contains "التالي"
        $response->assertSee('التالي');

        // Assert submit button is removed entirely from preview
        $response->assertDontSee('btn-submit-form');
    }
}
