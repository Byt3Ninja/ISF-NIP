<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;

class NotificationSettingsTest extends TestCase
{
    use WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create([
            'accept_email' => true,
            'accept_whatsapp' => false,
            'accept_pusher' => false,
        ]);
    }

    /** @test */
    public function user_can_view_notification_settings_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/dashboard/myProfile');

        $response->assertStatus(200);
        $response->assertSee('Notification Settings');
    }

    /** @test */
    public function user_can_update_notification_settings()
    {
        $response = $this->actingAs($this->user)
            ->post('/myProfile/notification-settings', [
                'accept_email' => false,
                'accept_whatsapp' => true,
                'accept_pusher' => true,
            ]);

        $response->assertRedirect('/dashboard/myProfile');
        
        $this->user->refresh();
        
        $this->assertFalse($this->user->accept_email);
        $this->assertTrue($this->user->accept_whatsapp);
        $this->assertTrue($this->user->accept_pusher);
    }

    /** @test */
    public function notification_settings_validation_works()
    {
        $response = $this->actingAs($this->user)
            ->post('/myProfile/notification-settings', [
                'accept_email' => 'invalid_value',
                'accept_whatsapp' => 'invalid_value',
                'accept_pusher' => 'invalid_value',
            ]);

        $response->assertSessionHasErrors(['accept_email', 'accept_whatsapp', 'accept_pusher']);
    }

    /** @test */
    public function notification_settings_defaults_are_applied()
    {
        $response = $this->actingAs($this->user)
            ->post('/myProfile/notification-settings', []);

        $response->assertRedirect('/dashboard/myProfile');
        
        $this->user->refresh();
        
        // Defaults should be applied
        $this->assertTrue($this->user->accept_email);
        $this->assertFalse($this->user->accept_whatsapp);
        $this->assertFalse($this->user->accept_pusher);
    }
}
