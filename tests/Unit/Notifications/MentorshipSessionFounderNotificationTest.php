<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\User;
use App\Notifications\MentorshipSessionFounderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class MentorshipSessionFounderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        return $app;
    }

    /** @test */
    public function it_sends_english_email_when_founder_preferred_language_is_english()
    {
        $founder = User::factory()->create([
            'language' => 'en',
            'email' => 'founder@example.com',
        ]);

        $sessionData = [
            'mentor_name' => 'John Doe',
            'session_date' => '2026-07-20',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'session_type' => 'Online',
            'meeting_link' => 'https://zoom.us/j/123456',
        ];

        $notification = new MentorshipSessionFounderNotification($sessionData);

        $mailMessage = $notification->toMail($founder);

        $this->assertEquals('Mentorship Session with John Doe.', $mailMessage->subject);

        $rendered = $mailMessage->render();
        $this->assertStringContainsString('You have a new session(s) with John Doe.', $rendered);
        $this->assertStringContainsString('Mentor name: John Doe.', $rendered);
        $this->assertStringContainsString('Date(s): 2026-07-20.', $rendered);
        $this->assertStringContainsString('Time: 10:00:00 – 11:00:00 (AM/PM).', $rendered);
        $this->assertStringContainsString('Type: Online.', $rendered);
        $this->assertStringContainsString('Meeting Link: https://zoom.us/j/123456.', $rendered);
        $this->assertStringNotContainsString('Location:', $rendered);
    }

    /** @test */
    public function it_sends_arabic_email_when_founder_preferred_language_is_arabic()
    {
        $founder = User::factory()->create([
            'language' => 'ar',
            'email' => 'founder@example.com',
        ]);

        $sessionData = [
            'mentor_name' => 'John Doe',
            'session_date' => '2026-07-20',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'session_type' => 'Online',
            'meeting_link' => 'https://zoom.us/j/123456',
        ];

        $notification = new MentorshipSessionFounderNotification($sessionData);

        $mailMessage = $notification->toMail($founder);

        $this->assertEquals('جلسة إرشاد مع John Doe.', $mailMessage->subject);

        $rendered = $mailMessage->render();
        $this->assertStringContainsString('لديك جلسة إرشاد جديدة مع John Doe.', $rendered);
        $this->assertStringContainsString('اسم المرشد: John Doe.', $rendered);
        $this->assertStringContainsString('التاريخ: 2026-07-20.', $rendered);
        $this->assertStringContainsString('الوقت: 10:00:00 – 11:00:00 (AM/PM).', $rendered);
        $this->assertStringContainsString('النوع: أونلاين.', $rendered);
        $this->assertStringContainsString('رابط الاجتماع: https://zoom.us/j/123456.', $rendered);
        $this->assertStringContainsString('الإجراء: تفاصيل الجلسة.', $rendered);
        $this->assertStringNotContainsString('الموقع:', $rendered);
    }

    /** @test */
    public function it_renders_offline_details_when_session_type_is_offline()
    {
        $founder = User::factory()->create([
            'language' => 'en',
            'email' => 'founder@example.com',
        ]);

        $sessionData = [
            'mentor_name' => 'John Doe',
            'session_date' => '2026-07-20',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'session_type' => 'Offline',
            'location' => 'Main Office Room A',
        ];

        $notification = new MentorshipSessionFounderNotification($sessionData);

        $mailMessage = $notification->toMail($founder);
        $rendered = $mailMessage->render();

        $this->assertStringContainsString('Type: Offline.', $rendered);
        $this->assertStringContainsString('Location: Main Office Room A.', $rendered);
        $this->assertStringNotContainsString('Meeting Link:', $rendered);
    }
}
