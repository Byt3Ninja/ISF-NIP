<?php

namespace Tests\Feature;

use App\Cycle;
use App\Http\Middleware\LoginSecurityMiddleware;
use App\Organization;
use App\Permission;
use App\Role;
use App\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * GV-10295: an invalid Program Logo must block saving the program wizard,
 * on create (programs.general) and on edit (programs.generalEdit / programs.schedule).
 */
class ProgramLogoValidationTest extends TestCase
{
    use FakesOutboundServices;

    private const PERMISSIONS = ['programs.general', 'programs.generalEdit', 'programs.schedule'];

    private Organization $org;

    private Cycle $cycle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([LoginSecurityMiddleware::class, EnsureEmailIsVerified::class]);
        $this->fakeOutboundServices();
        Storage::fake('public_uploads');

        $this->org = Organization::factory()->create();
        $this->cycle = Cycle::factory()->create(['organization_id' => $this->org->id, 'img_url' => 'programs/existing.png']);

        $role = Role::create(['name' => 'admin', 'default_for' => 'admin']);
        $role->_permissions()->attach(Permission::whereIn('name', self::PERMISSIONS)->pluck('id'));
        $admin = User::factory()->create();
        $admin->allotRoleById($role->id);
        $this->actingAs($admin->fresh());
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'programOrganization' => $this->org->id,
            'programType' => 'general',
            'programTitle' => 'GV-10295 logo program',
            'programTitleAr' => 'برنامج الشعار',
            'programDateRange' => '2026-11-01 to 2026-12-01',
            'programStepType' => 'step',
        ];
    }

    public static function invalidLogos(): array
    {
        return [
            'pdf' => ['logo.pdf', 'application/pdf'],
            'zip' => ['logo.zip', 'application/zip'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider invalidLogos
     */
    public function create_rejects_an_invalid_logo_and_creates_nothing(string $name, string $mime)
    {
        $before = Cycle::count();

        $this->post(route('programs.general'), $this->payload([
            'programLogo' => UploadedFile::fake()->create($name, 10, $mime),
        ]), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('programLogo');

        $this->assertSame($before, Cycle::count());
    }

    /**
     * @test
     *
     * @dataProvider invalidLogos
     */
    public function edit_rejects_an_invalid_logo_and_keeps_the_current_one(string $name, string $mime)
    {
        foreach (['programs.generalEdit', 'programs.schedule'] as $route) {
            $this->post(route($route, $this->cycle->id), $this->payload([
                'programLogo' => UploadedFile::fake()->create($name, 10, $mime),
            ]), ['Accept' => 'application/json'])
                ->assertStatus(422)
                ->assertJsonValidationErrors('programLogo');

            $this->assertSame('programs/existing.png', $this->cycle->fresh()->img_url, $route);
        }
    }

    /** @test */
    public function a_failed_upload_without_schedule_fields_is_still_rejected()
    {
        // PHP leaves tmp_name empty when a file exceeds upload_max_filesize, so hasFile() is false
        $failed = new UploadedFile('', 'logo.png', 'image/png', UPLOAD_ERR_INI_SIZE, true);
        $payload = array_diff_key($this->payload(), array_flip(['programDateRange', 'programStepType']));

        $this->post(route('programs.generalEdit', $this->cycle->id), $payload + ['programLogo' => $failed], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('programLogo');

        $this->assertSame('programs/existing.png', $this->cycle->fresh()->img_url);
    }

    /** @test */
    public function a_valid_logo_is_saved()
    {
        $this->post(route('programs.schedule', $this->cycle->id), $this->payload([
            'programLogo' => UploadedFile::fake()->image('logo.png', 100, 100),
        ]), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonStructure(['logo_url']);

        $imgUrl = $this->cycle->fresh()->img_url;
        $this->assertNotSame('programs/existing.png', $imgUrl);
        Storage::disk('public_uploads')->assertExists($imgUrl);
    }
}
