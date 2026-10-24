<?php

namespace Tests\Feature\Correctness;

use Illuminate\Support\Facades\Route;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * C13-C16: four routes pointed at controller methods that do not exist, so every hit
 * was a 500. Nothing in the app calls them; they are removed.
 */
class MissingRoutesTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    public function test_dead_route_names_are_gone(): void
    {
        foreach (['js.translations', 'activity.log', 'user.profile.interests_manage'] as $name) {
            $this->assertFalse(Route::has($name), "route {$name} still registered");
        }
    }

    public function test_dead_uris_no_longer_500(): void
    {
        $this->get('/js-translations/en')->assertNotFound();

        $this->actingAs($this->actor('admin'));
        $this->post('/dashboard/log-activity')->assertNotFound();
        $this->get('/dashboard/interests')->assertNotFound();
        $this->post('/dashboard/mread')->assertNotFound();
    }
}
