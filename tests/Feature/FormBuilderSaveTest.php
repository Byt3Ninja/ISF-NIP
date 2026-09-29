<?php

namespace Tests\Feature;

use App\User;
use App\Organization;
use App\BuilderForm;
use Database\Seeders\FieldSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FormBuilderSaveTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed fields
        $this->seed(FieldSeeder::class);
        $this->withoutMiddleware(\App\Http\Middleware\LoginSecurityMiddleware::class);

        // Create default settings to avoid 500 error on views (using DB::table to bypass Eloquent fillable guards)
        \DB::table('settings')->insert([
            'id' => 1,
            'type' => '1',
            'title' => 'Test Platform',
            'ar_title' => 'منصة اختبار',
            'assessment' => 1,
        ]);

        // Manually setup Admin role and form builder permissions
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
        $role->permissions()->syncWithoutDetaching([$p1->id, $p2->id, $p3->id]);
        
        // Forget cached permissions to ensure they are reloaded
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_can_save_form_with_multiple_sections(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');

        $org = Organization::create([
            'name_en' => 'Test Org EN',
            'name_ar' => 'Test Org AR',
            'user_id' => $user->id,
        ]);

        $payload = [
            'form_fields_data' => [
                'title' => 'Test Multisection Form',
                'organization_id' => $org->id,
                'items' => [
                    [
                        'type' => 'section',
                        'title' => 'Section A',
                        'description' => 'First Section description',
                        'logic' => 'continue',
                        'questions' => [
                            [
                                'title' => 'Question A1',
                                'question_type' => 'short_answer',
                                'required' => true,
                                'logic' => [],
                            ],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'title' => 'Section B',
                        'description' => 'Second Section description',
                        'logic' => 'continue',
                        'questions' => [
                            [
                                'title' => 'Question B1',
                                'question_type' => 'paragraph',
                                'required' => false,
                                'logic' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->postJson(route('forms.design_save', ['id' => 0]), $payload);

        $response->dump();

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $formId = $response->json('id');
        $form = BuilderForm::findOrFail($formId);

        $this->assertEquals('Test Multisection Form', $form->form_name);
        $this->assertEquals($org->id, $form->organization_id);

        $fields = $form->fields()->withPivot('options', 'sort_order')->orderBy('builder_fields.sort_order', 'asc')->get();

        // There should be 2 questions in total across both sections
        $this->assertCount(2, $fields);

        // First question details
        $q1 = $fields[0];
        $options1 = json_decode($q1->pivot->options, true);
        $this->assertEquals('Question A1', $options1['label']);
        $this->assertEquals('Section A', $options1['section']);
        $this->assertEquals('First Section description', $options1['description']);

        // Second question details
        $q2 = $fields[1];
        $options2 = json_decode($q2->pivot->options, true);
        $this->assertEquals('Question B1', $options2['label']);
        $this->assertEquals('Section B', $options2['section']);
        $this->assertEquals('Second Section description', $options2['description']);
    }

    public function test_untitled_sections_are_saved_with_default_names_or_handled_correctly(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');

        $org = Organization::create([
            'name_en' => 'Test Org EN',
            'name_ar' => 'Test Org AR',
            'user_id' => $user->id,
        ]);

        $payload = [
            'form_fields_data' => [
                'title' => 'Test Untitled Sections',
                'organization_id' => $org->id,
                'items' => [
                    [
                        'type' => 'section',
                        'title' => '',
                        'description' => 'First description',
                        'logic' => 'continue',
                        'questions' => [
                            [
                                'title' => 'Question A1',
                                'question_type' => 'short_answer',
                                'required' => true,
                                'logic' => [],
                            ],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'title' => '',
                        'description' => 'Second description',
                        'logic' => 'continue',
                        'questions' => [
                            [
                                'title' => 'Question B1',
                                'question_type' => 'paragraph',
                                'required' => false,
                                'logic' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)
            ->postJson(route('forms.design_save', ['id' => 0]), $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $formId = $response->json('id');

        // Let's call showBuilder (which returns grouped_data) and see if we get 1 or 2 sections!
        $responseBuilder = $this->actingAs($user)
            ->get(route('forms.builder_view', ['id' => $formId]));

        $responseBuilder->dump();

        $responseBuilder->assertStatus(200);
        $builderData = $responseBuilder->viewData('data')['builder_data'];

        // If the untitled sections are merged, builder_data will have only 1 section!
        // Let's check how many sections are returned.
        $this->assertCount(2, $builderData);
    }

    public function test_form_submission_only_validates_active_path_questions(): void
    {
        $user = User::factory()->create();
        $user->allotRole('Admin');

        $org = Organization::create([
            'name_en' => 'Test Org EN',
            'name_ar' => 'Test Org AR',
            'user_id' => $user->id,
        ]);

        // Create a form with 2 sections:
        // Section 1 has Question A1 (mandatory, multiple choice with routing: Option A -> submit, Option B -> Section 2)
        // Section 2 has Question B1 (mandatory, paragraph)
        $payload = [
            'form_fields_data' => [
                'title' => 'Test Conditional Routing',
                'organization_id' => $org->id,
                'items' => [
                    [
                        'type' => 'section',
                        'title' => 'Section 1',
                        'description' => 'S1 desc',
                        'logic' => 'continue',
                        'questions' => [
                            [
                                'title' => 'Route Choice',
                                'question_type' => 'multiple_choice',
                                'required' => true,
                                'options' => ['Option A', 'Option B'],
                                'logic' => [
                                    'Option A' => 'submit',
                                    'Option B' => 'section-2',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'section',
                        'title' => 'Section 2',
                        'description' => 'S2 desc',
                        'logic' => 'continue',
                        'questions' => [
                            [
                                'title' => 'Mandatory paragraph B1',
                                'question_type' => 'paragraph',
                                'required' => true,
                                'logic' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Save the form
        $response = $this->actingAs($user)
            ->postJson(route('forms.design_save', ['id' => 0]), $payload);

        $response->assertStatus(200);
        $formId = $response->json('id');
        $form = BuilderForm::findOrFail($formId);

        $builderFields = \DB::table('builder_fields')
            ->where('builder_form_id', $formId)
            ->orderBy('sort_order', 'asc')
            ->get();
        $q1_id = $builderFields[0]->id;
        $q2_id = $builderFields[1]->id;

        // Scenario 1: Respondent selects "Option A" (active path is just Section 1, skipping Section 2)
        // They submit Route Choice = Option A, and field_ids = only route choice ID
        $submitPayload1 = [
            'builder_form_id' => $formId,
            'field_ids' => (string) $q1_id,
            'field_' . $q1_id => 'Option A',
        ];

        $responseSubmit1 = $this->actingAs($user)
            ->post(route('forms.submissions_create'), $submitPayload1);

        // Validation should succeed because the mandatory B1 question in Section 2 is NOT in the submitted field_ids
        $responseSubmit1->assertRedirect();
        $responseSubmit1->assertSessionHas('BuilderForm work correctly.', 1);

        // Scenario 2: Respondent submits field_ids including Section 2's question but B1 is empty
        $submitPayload2 = [
            'builder_form_id' => $formId,
            'field_ids' => $q1_id . ',' . $q2_id,
            'field_' . $q1_id => 'Option B',
            'field_' . $q2_id => '',
        ];

        $responseSubmit2 = $this->actingAs($user)
            ->post(route('forms.submissions_create'), $submitPayload2);

        // Validation should fail because B1 is mandatory and is included in field_ids
        $responseSubmit2->assertSessionHasErrors(['field_' . $q2_id]);
    }
}
