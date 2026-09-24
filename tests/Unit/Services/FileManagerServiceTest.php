<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FileManagerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;


class FileManagerServiceTest extends TestCase
{

    protected FileManagerService $fileManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileManager = new FileManagerService();
        Storage::fake('public');
        Storage::fake('s3');
    }

    /** @test */
    public function it_can_upload_a_single_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 500); // 500KB

        $path = $this->fileManager->upload($file, 'documents', 'public');

        $this->assertNotEmpty($path);
        $this->assertStringContainsString('documents/', $path);
        Storage::disk('public')->assertExists($path);
    }

    /** @test */
    public function it_can_upload_multiple_files()
    {
        $files = [
            UploadedFile::fake()->create('doc1.pdf', 100),
            UploadedFile::fake()->create('doc2.pdf', 200),
        ];

        $paths = $this->fileManager->uploadMultiple($files, 'documents', 'public');

        $this->assertCount(2, $paths);
        foreach ($paths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    /** @test */
    public function it_can_update_a_file_by_deleting_the_old_one()
    {
        $oldFile = UploadedFile::fake()->create('old.pdf', 100);
        $oldPath = $this->fileManager->upload($oldFile, 'documents', 'public');
        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->create('new.pdf', 200);
        $newPath = $this->fileManager->update($newFile, $oldPath, 'documents', 'public');

        $this->assertNotEquals($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    /** @test */
    public function it_can_delete_a_single_file()
    {
        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $path = $this->fileManager->upload($file, 'documents', 'public');
        Storage::disk('public')->assertExists($path);

        $deleted = $this->fileManager->delete($path, 'public');

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($path);
    }

    /** @test */
    public function it_can_delete_multiple_files()
    {
        $file1 = UploadedFile::fake()->create('doc1.pdf', 100);
        $file2 = UploadedFile::fake()->create('doc2.pdf', 100);
        $path1 = $this->fileManager->upload($file1, 'documents', 'public');
        $path2 = $this->fileManager->upload($file2, 'documents', 'public');

        $deleted = $this->fileManager->delete([$path1, $path2], 'public');

        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertMissing($path2);
    }

    /** @test */
    public function it_validates_file_successfully()
    {
        $file = UploadedFile::fake()->create('image.png', 500);

        $isValid = $this->fileManager->validate($file, ['file' => 'max:1024']);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_throws_validation_exception_on_invalid_file()
    {
        $file = UploadedFile::fake()->create('large_image.png', 20000); // 20MB

        $this->expectException(ValidationException::class);

        $this->fileManager->validate($file, ['file' => 'max:10240']); // Max 10MB
    }

    /** @test */
    public function it_rejects_blacklisted_extensions_during_upload()
    {
        $file = UploadedFile::fake()->create('hacked.php', 100);

        $this->expectException(ValidationException::class);

        $this->fileManager->upload($file, 'documents', 'public');
    }

    /** @test */
    public function it_rejects_blacklisted_mimes_during_upload()
    {
        // SVG is blacklisted for stored XSS prevention
        $file = UploadedFile::fake()->create('vector.svg', 100, 'image/svg+xml');

        $this->expectException(ValidationException::class);

        $this->fileManager->upload($file, 'documents', 'public');
    }

    /** @test */
    public function it_can_get_full_url_from_relative_path()
    {
        $path = 'documents/sample.pdf';
        $url = $this->fileManager->getUrl($path, 'public');

        $this->assertEquals(Storage::disk('public')->url($path), $url);
    }

    /** @test */
    public function it_preserves_full_url_if_already_absolute()
    {
        $absoluteUrl = 'https://example.com/somefile.pdf';
        $url = $this->fileManager->getUrl($absoluteUrl, 'public');

        $this->assertEquals($absoluteUrl, $url);
    }

    /** @test */
    public function it_extracts_relative_path_from_full_url()
    {
        $path = 'documents/sample.pdf';
        $url = Storage::disk('public')->url($path);

        $extractedPath = $this->fileManager->getRelativePath($url, 'public');

        $this->assertEquals($path, $extractedPath);
    }

    /** @test */
    public function it_strips_legacy_root_folder_prefix_if_present()
    {
        config(['filesystems.disks.test_disk' => [
            'driver' => 'local',
            'root' => storage_path('app/pitch_decks'),
            'url' => 'http://localhost/storage/pitch_decks',
        ]]);

        $path = 'pitch_decks/12/sample.pdf';
        $extractedPath = $this->fileManager->getRelativePath($path, 'test_disk');

        $this->assertEquals('12/sample.pdf', $extractedPath);
    }

    /** @test */
    public function it_can_migrate_database_urls_to_relative_paths()
    {
        config(['filesystems.disks.public_uploads.url' => 'https://localhost:8000/191014']);

        $user = \App\User::factory()->create([
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
            'email' => 'john@example.com',
            'img_url' => 'https://localhost:8000/191014/avatars/john.jpg'
        ]);

        $this->artisan('files:migrate-to-paths', [
            '--model' => 'App\User',
            '--column' => ['img_url'],
            '--disk' => 'public_uploads'
        ])->assertExitCode(0);

        $user->refresh();
        $this->assertEquals('avatars/john.jpg', $user->getRawOriginal('img_url'));
    }

    /** @test */
    public function it_deletes_associated_files_on_model_deletion()
    {
        $file = UploadedFile::fake()->create('logo.png', 100);
        $fileManager = new FileManagerService();
        $path = $fileManager->upload($file, 'logos', 'public');
        Storage::disk('public')->assertExists($path);

        $model = new class extends Model {
            use \App\Traits\HasFileCleanup;
            protected $table = 'users';
            protected array $fileFields = [
                'logo_path' => 'public',
            ];
            
            public function getRawOriginal($key = null, $default = null)
            {
                if ($key === 'logo_path') {
                    return $this->attributes['logo_path'] ?? null;
                }
                return parent::getRawOriginal($key, $default);
            }
        };

        $model->logo_path = $path;
        $model->deleteAssociatedFiles();

        Storage::disk('public')->assertMissing($path);
    }
}
