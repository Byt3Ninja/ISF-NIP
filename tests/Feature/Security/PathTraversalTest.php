<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\User;
use App\Role;
use App\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PathTraversalTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAsAuthenticatedUser(): User
    {
        Role::factory()->create(['name' => 'Registered', 'default_for' => 'registered']);
        $user = User::factory()->create();
        $user->allotRole('registered');
        $this->actingAs($user);
        return $user;
    }

    /** @test */
    public function showfile_blocks_path_traversal()
    {
        $user = $this->actingAsAuthenticatedUser();

        $response = $this->get('/filemanager/showfile/' . $user->id . '/../../../../.env');

        $this->assertContains($response->getStatusCode(), [401, 403, 404]);
    }

    /** @test */
    public function showimage_blocks_path_traversal()
    {
        $user = $this->actingAsAuthenticatedUser();

        $response = $this->get('/filemanager/showimage/' . $user->id . '/..%2F..%2F..%2F..%2F.env');

        $this->assertContains($response->getStatusCode(), [401, 403, 404]);
    }

    /** @test */
    public function trainingfile_blocks_path_traversal()
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->get('/training/files/../../../../etc/passwd');

        $this->assertContains($response->getStatusCode(), [401, 403, 404]);
    }

    /** @test */
    public function trainingimage_blocks_path_traversal()
    {
        $this->actingAsAuthenticatedUser();

        $response = $this->get('/training/photos/../../../../etc/passwd');

        $this->assertContains($response->getStatusCode(), [401, 403, 404]);
    }

    /** @test */
    public function showfile_sanitizes_filename_to_basename()
    {
        $user = $this->actingAsAuthenticatedUser();

        $response = $this->get('/filemanager/showfile/' . $user->id . '/subdir/../../../.env');

        $this->assertContains($response->getStatusCode(), [401, 403, 404]);
    }
}
