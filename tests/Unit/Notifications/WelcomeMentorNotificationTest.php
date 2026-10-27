<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\User;
use App\Settings;
use App\Notifications\WelcomeMentorNotification;
use Illuminate\Support\Facades\Lang;

class WelcomeMentorNotificationTest extends TestCase
{
    public function test_via_includes_database_channel()
    {
        $notifiable = new User();

        $this->assertContains('database', (new WelcomeMentorNotification($notifiable))->via($notifiable));
    }

    public function test_database_representation_follows_platform_language()
    {
        $notifiable = new User();
        $notifiable->id = 123;

        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';

        $data = (new WelcomeMentorNotification($notifiable))->toArray($notifiable);

        $this->assertSame(Lang::get('notifications.welcome.database', [], $locale), $data['message']);
        $this->assertSame(123, $data['user_id']);
    }
}
