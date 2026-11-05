<?php

namespace Tests\Feature;

use App\BuilderField;
use App\BuilderForm;
use App\Cycle;
use App\Http\Middleware\LoginSecurityMiddleware;
use App\Organization;
use App\Permission;
use App\Role;
use App\Step;
use App\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Tests\TestCase;

/**
 * GV-10420: sections survive a save → reload as the author arranged them —
 * repeated or blank titles do not merge, and each section's "After this
 * section" jump is kept — in the builder and on the form the applicant sees.
 */
class FormBuilderSectionsTest extends TestCase
{
    private Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([LoginSecurityMiddleware::class, EnsureEmailIsVerified::class]);
        $this->org = Organization::factory()->create();

        $groups = json_decode(file_get_contents(base_path('database/dbFiles/rolesWithDefaultPermissions.json')), true);
        $names = collect($groups)->flatMap(fn ($g) => $g['permissions'])
            ->filter(fn ($p) => in_array('admin', $p['default_for'], true))->pluck('name')->unique();
        $role = Role::create(['name' => 'admin', 'default_for' => 'admin']);
        $role->_permissions()->attach(Permission::whereIn('name', $names)->pluck('id'));
        $admin = User::factory()->create();
        $admin->allotRoleById($role->id);
        $this->actingAs($admin->fresh());
    }

    private function question(string $title): array
    {
        return ['type' => 'question', 'title' => $title, 'question_type' => 'short_answer', 'required' => false, 'options' => [], 'logic' => []];
    }

    private function saveThreeSections(): int
    {
        $response = $this->postJson(route('forms.design_save', ['id' => 0]), ['form_fields_data' => [
            'title' => 'GV-10420 sections',
            'organization_id' => $this->org->id,
            'items' => [
                ['type' => 'section', 'title' => 'Details', 'description' => '', 'logic' => 'section-2', 'questions' => [$this->question('Q1')]],
                ['type' => 'section', 'title' => 'Details', 'description' => '', 'logic' => 'submit', 'questions' => [$this->question('Q2'), $this->question('Q3')]],
                ['type' => 'section', 'title' => '', 'description' => '', 'logic' => 'not-a-target', 'questions' => [$this->question('Q4')]],
            ],
        ]])->assertOk()->assertJson(['success' => true]);

        return $response->json('id');
    }

    public function test_save_stores_each_sections_position_and_jump(): void
    {
        $id = $this->saveThreeSections();

        $stored = BuilderField::where('builder_form_id', $id)->orderBy('sort_order')->get()
            ->map(fn ($f) => [$f->options->label, $f->options->section, $f->options->section_index, $f->options->section_logic])->all();

        $this->assertSame([
            ['Q1', 'Details', 0, 'section-2'],
            ['Q2', 'Details', 1, 'submit'],
            ['Q3', 'Details', 1, 'submit'],
            ['Q4', 'Section 3', 2, 'continue'],   // unknown target normalised
        ], $stored);
    }

    public function test_builder_reload_keeps_sections_apart_with_their_jumps(): void
    {
        $id = $this->saveThreeSections();

        $html = $this->get('/dashboard/show-builder/' . $id . '?org_id=' . $this->org->id)->assertOk()->getContent();
        $this->assertSame(1, preg_match('/builderData: (\[.*?\]),\n/s', $html, $m));
        $sections = json_decode($m[1], true);

        $this->assertSame(['Details', 'Details', 'Section 3'], array_column($sections, 'title'));
        $this->assertSame(['section-2', 'submit', 'continue'], array_column($sections, 'logic'));
        $this->assertSame([['Q1'], ['Q2', 'Q3'], ['Q4']], array_map(fn ($s) => array_column($s['questions'], 'label'), $sections));
    }

    public function test_saved_form_preview_renders_every_section_with_its_jump(): void
    {
        $id = $this->saveThreeSections();

        $html = $this->get(route('forms.render_view', $id))->assertOk()->getContent();
        preg_match_all('/data-section-logic="([^"]*)" data-section-id="(\d+)"/', $html, $m);

        $this->assertSame(['section-2', 'submit', 'continue'], $m[1]);
        $this->assertSame(['0', '1', '2'], $m[2]);
    }

    public function test_switch_form_list_names_each_program_once(): void
    {
        $id = $this->saveThreeSections();
        $cycle = Cycle::factory()->create(['organization_id' => $this->org->id]);
        foreach ([1, 2] as $n) {
            Step::factory()->create(['cycle_id' => $cycle->id, 'data' => $id]);
        }

        $this->assertSame([$cycle->id], BuilderForm::find($id)->cycles->pluck('id')->all());
    }
}
