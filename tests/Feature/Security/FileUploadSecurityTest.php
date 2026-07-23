<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Role;
use App\Settings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;

class FileUploadSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private function setupRegistration(): void
    {
        Settings::factory()->create(['id' => 1, 'VFEmail' => 0]);
        Role::firstOrCreate(['name' => 'Registered'], ['default_for' => 'Registered']);
    }

    /** @test */
    public function rejects_php_extension_in_base64_upload()
    {
        Storage::fake('companies');
        $this->setupRegistration();

        $maliciousPayload = 'data:image/php;base64,' . base64_encode('<?php echo "hacked"; ?>');

        $this->postJson('/api/register-company', [
            'personal' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'secupload1@gmail.com',
                'phone' => '1234567890',
            ],
            'company' => [
                'business_or_product_name' => 'Test Company',
                'product_photo' => [$maliciousPayload],
            ],
        ]);

        $files = Storage::disk('companies')->allFiles();
        $phpFiles = array_filter($files, fn($f) => str_contains($f, '.php'));
        $this->assertEmpty($phpFiles, 'PHP file should not be stored on disk');
    }

    /** @test */
    public function accepts_valid_image_in_base64_upload()
    {
        Storage::fake('companies');
        $this->setupRegistration();

        $img = imagecreatetruecolor(1, 1);
        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        $validPayload = 'data:image/png;base64,' . base64_encode($pngData);

        $this->postJson('/api/register-company', [
            'personal' => [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'secupload2@gmail.com',
                'phone' => '1234567891',
            ],
            'company' => [
                'business_or_product_name' => 'Valid Company',
                'product_photo' => [$validPayload],
            ],
        ]);

        $files = Storage::disk('companies')->allFiles();
        $pngFiles = array_filter($files, fn($f) => str_ends_with($f, '.png'));
        $this->assertNotEmpty($pngFiles, 'Valid PNG should be stored');
    }

    /** @test */
    public function rejects_executable_content_disguised_as_image()
    {
        Storage::fake('companies');
        $this->setupRegistration();

        $maliciousPayload = 'data:image/png;base64,' . base64_encode('<?php system($_GET["cmd"]); ?>');

        $this->postJson('/api/register-company', [
            'personal' => [
                'first_name' => 'Test',
                'last_name' => 'Hacker',
                'email' => 'secupload3@gmail.com',
                'phone' => '1234567892',
            ],
            'company' => [
                'business_or_product_name' => 'Evil Corp',
                'product_photo' => [$maliciousPayload],
            ],
        ]);

        $files = Storage::disk('companies')->allFiles();
        $this->assertEmpty($files, 'Executable content disguised as image should be rejected');
    }
}
