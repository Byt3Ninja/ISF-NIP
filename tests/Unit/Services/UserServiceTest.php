<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mockery;

class UserServiceTest extends TestCase
{

    protected UserService $userService;
    protected $userRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the UserRepository
        $this->userRepositoryMock = Mockery::mock(UserRepository::class);
        $this->app->instance(UserRepository::class, $this->userRepositoryMock);
        
        $this->userService = new UserService();
        // Use reflection to inject mocked repository
        $reflection = new \ReflectionClass($this->userService);
        $property = $reflection->getProperty('userRepository');
        $property->setAccessible(true);
        $property->setValue($this->userService, $this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_list_users_with_pagination()
    {
        $filters = ['per_page' => 10, 'page' => 1];
        $queryMock = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $paginatedUsers = User::factory()->count(10)->make();
        
        $this->userRepositoryMock
            ->shouldReceive('filter')
            ->once()
            ->with($filters)
            ->andReturn($queryMock);
            
        $this->userRepositoryMock
            ->shouldReceive('paginate')
            ->once()
            ->with($queryMock, 10, 1, 'desc')
            ->andReturn($paginatedUsers);

        $result = $this->userService->index($filters, true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    /** @test */
    public function it_can_list_users_without_pagination()
    {
        $filters = [];
        $queryMock = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $users = User::factory()->count(5)->make();
        
        $this->userRepositoryMock
            ->shouldReceive('filter')
            ->once()
            ->with($filters)
            ->andReturn($queryMock);
            
        $queryMock->shouldReceive('get')
            ->once()
            ->andReturn($users);

        $result = $this->userService->index($filters, false);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }

    /** @test */
    public function it_can_show_a_user_by_id()
    {
        $user = User::factory()->create();
        
        $result = $this->userService->show($user->id);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_user_not_found()
    {
        $result = $this->userService->show(99999);

        $this->assertNull($result);
    }

    /** @test */
    public function it_can_store_a_new_user()
    {
        Storage::fake('UsersLFM');
        
        $role = Role::factory()->create(['name' => 'Registered']);
        $userData = [
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
            'role_id' => $role->id,
        ];

        $createdUser = User::factory()->make($userData);
        $createdUser->id = 1;

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($userData) {
                return $data['email'] === $userData['email'];
            }))
            ->andReturn([
                'success' => true,
                'data' => $createdUser
            ]);

        $result = $this->userService->store($userData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        Storage::disk('UsersLFM')->assertExists("1");
    }

    /** @test */
    public function it_handles_user_creation_failure()
    {
        $userData = [
            'first_name_en' => 'John',
            'email' => 'john@example.com',
        ];

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $result = $this->userService->store($userData);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }

    /** @test */
    public function it_can_update_a_user()
    {
        $user = User::factory()->create();
        $updateData = [
            'first_name_en' => 'Jane',
            'last_name_en' => 'Smith',
        ];

        $updatedUser = $user->replicate();
        $updatedUser->fill($updateData);

        $this->userRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($user, $updateData)
            ->andReturn($updatedUser);

        $result = $this->userService->update($user, $updateData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user', $result);
    }

    /** @test */
    public function it_handles_user_update_failure()
    {
        $user = User::factory()->create();
        $updateData = ['email' => 'invalid-email'];

        $this->userRepositoryMock
            ->shouldReceive('update')
            ->once()
            ->andThrow(new \Exception('Validation error'));

        $result = $this->userService->update($user, $updateData);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals(500, $result['status']);
    }

    /** @test */
    public function it_can_deactivate_a_user()
    {
        $user = User::factory()->create(['status' => 'active']);
        $authUser = User::factory()->create();

        $this->userRepositoryMock
            ->shouldReceive('deactivate')
            ->once()
            ->with($user)
            ->andReturn(true);

        $result = $this->userService->deactivateUser($user, $authUser->id);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_prevents_deactivating_self()
    {
        $user = User::factory()->create();

        $result = $this->userService->deactivateUser($user, $user->id);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals(403, $result['status']);
    }

    /** @test */
    public function it_can_get_role_list()
    {
        $roles = Role::factory()->count(5)->create();

        $this->userRepositoryMock
            ->shouldReceive('roles')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => $roles
            ]);

        $result = $this->userService->roleList();

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_update_user_password()
    {
        $user = User::factory()->create();
        $passwordData = [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $this->userRepositoryMock
            ->shouldReceive('updatePassword')
            ->once()
            ->with($user, $passwordData)
            ->andReturn([
                'success' => true,
                'message' => 'Password updated'
            ]);

        $result = $this->userService->updatePassword($user, $passwordData);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_destroy_a_user()
    {
        $user = User::factory()->create();

        $this->userRepositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($user)
            ->andReturn(true);

        $result = $this->userService->destroy($user);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_companies_for_registered_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Registered');
        $requestData = ['search' => 'test'];

        $this->userRepositoryMock
            ->shouldReceive('getCompaniesForRegisteredUser')
            ->once()
            ->with($user, $requestData)
            ->andReturn([
                'companies' => collect([]),
                'errorMessage' => null
            ]);

        $result = $this->userService->getCompanies($requestData, $user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('companies', $result);
    }

    /** @test */
    public function it_can_import_users_from_file()
    {
        Storage::fake('local');
        
        // Create a real Excel file for testing
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Add header row
        $sheet->setCellValue('A1', 'First Name');
        $sheet->setCellValue('B1', 'Last Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Phone');
        
        // Add data rows
        $sheet->setCellValue('A2', 'John');
        $sheet->setCellValue('B2', 'Doe');
        $sheet->setCellValue('C2', 'john@example.com');
        $sheet->setCellValue('D2', '1234567890');
        
        $sheet->setCellValue('A3', 'Jane');
        $sheet->setCellValue('B3', 'Smith');
        $sheet->setCellValue('C3', 'jane@example.com');
        $sheet->setCellValue('D3', '0987654321');
        
        // Save to temporary file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'test_users_');
        $tempFile .= '.xlsx';
        $writer->save($tempFile);
        
        // Create UploadedFile from the real Excel file
        $file = new UploadedFile(
            $tempFile,
            'users.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $this->userRepositoryMock
            ->shouldReceive('importUsers')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn([
                'success' => true,
                'message' => 'Users imported successfully'
            ]);

        $result = $this->userService->importUsers($file);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        
        // Clean up
        @unlink($tempFile);
    }

    /** @test */
    public function it_handles_import_file_processing_error()
    {
        $invalidFile = UploadedFile::fake()->create('invalid.txt', 100);

        $result = $this->userService->importUsers($invalidFile);

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
}

