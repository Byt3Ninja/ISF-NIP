<?php

namespace Tests\Unit\Notifications;

use App\Company;
use App\User;
use App\Notifications\MentorshipSessionMentorBookingNotification;
use Tests\TestCase;

class MentorshipSessionMentorBookingNotificationTest extends TestCase
{

    public function test_it_sends_english_email_when_mentors_preferred_language_is_english()
    {
        // 1. Create Mentor with English preferred language
        $mentor = User::factory()->create([
            'first_name_en' => 'John',
            'last_name_en' => 'Doe',
            'language' => 'en',
            'email' => 'mentor@example.com'
        ]);

        // 2. Create Founder
        $founder = User::factory()->create([
            'first_name_en' => 'Alice',
            'last_name_en' => 'Smith',
        ]);

        // 3. Create Company for Founder
        $company = Company::factory()->create([
            'name_en' => 'Alice Tech',
            'cycle' => 1
        ]);
        $founder->company()->associate($company);
        $founder->save();

        // 4. Create dummy sessions array
        $sessions = [
            [
                'date' => '2026-07-15',
                'start_time' => '10:00 AM',
                'end_time' => '11:00 AM',
                'type' => 'Online',
                'location' => null,
                'link' => 'https://zoom.us/j/12345',
                'is_hackathon' => false
            ]
        ];

        // 5. Instantiate notification
        $notification = new MentorshipSessionMentorBookingNotification($founder, $sessions);

        // 6. Get mail representation
        $mail = $notification->toMail($mentor);

        // 7. Assertions
        $this->assertEquals('Mentorship Session with Alice Smith.', $mail->subject);
        $rendered = $mail->render();
        
        $this->assertStringContainsString('You have a new session(s) with Alice Smith.', $rendered);
        $this->assertStringContainsString('Founder name:', $rendered);
        $this->assertStringContainsString('Startup name:', $rendered);
        $this->assertStringContainsString('Alice Tech', $rendered);
        $this->assertStringContainsString('Date (S):', $rendered);
        $this->assertStringContainsString('2026-07-15', $rendered);
        $this->assertStringContainsString('Time (s):', $rendered);
        $this->assertStringContainsString('10:00 AM – 11:00 AM', $rendered);
        $this->assertStringContainsString('Meeting Link:', $rendered);
        $this->assertStringContainsString('https://zoom.us/j/12345', $rendered);
    }

    public function test_it_sends_arabic_email_when_mentors_preferred_language_is_arabic()
    {
        // 1. Create Mentor with Arabic preferred language
        $mentor = User::factory()->create([
            'first_name_ar' => 'أحمد',
            'last_name_ar' => 'علي',
            'language' => 'ar',
            'email' => 'mentor@example.com'
        ]);

        // 2. Create Founder
        $founder = User::factory()->create([
            'first_name_ar' => 'سارة',
            'last_name_ar' => 'أحمد',
            'first_name_en' => 'Sara',
            'last_name_en' => 'Ahmed',
        ]);

        // 3. Create Company for Founder
        $company = Company::factory()->create([
            'name_ar' => 'سارة تك',
            'cycle' => 1
        ]);
        $founder->company()->associate($company);
        $founder->save();

        // 4. Create dummy sessions array
        $sessions = [
            [
                'date' => '2026-07-15',
                'start_time' => '10:00 AM',
                'end_time' => '11:00 AM',
                'type' => 'Offline',
                'location' => 'Riyadh HQ',
                'link' => null,
                'is_hackathon' => false
            ]
        ];

        // 5. Instantiate notification
        $notification = new MentorshipSessionMentorBookingNotification($founder, $sessions);

        // 6. Get mail representation
        $mail = $notification->toMail($mentor);

        // 7. Assertions
        $this->assertEquals('جلسة إرشاد مع سارة أحمد.', $mail->subject);
        $rendered = $mail->render();
        
        $this->assertStringContainsString('لديك جلسة إرشاد جديدة مع سارة أحمد.', $rendered);
        $this->assertStringContainsString('اسم المؤسس:', $rendered);
        $this->assertStringContainsString('اسم الشركة الناشئة:', $rendered);
        $this->assertStringContainsString('سارة تك', $rendered);
        $this->assertStringContainsString('التاريخ:', $rendered);
        $this->assertStringContainsString('2026-07-15', $rendered);
        $this->assertStringContainsString('الوقت:', $rendered);
        $this->assertStringContainsString('10:00 AM – 11:00 AM', $rendered);
        $this->assertStringContainsString('الموقع:', $rendered);
        $this->assertStringContainsString('Riyadh HQ', $rendered);
    }

    public function test_notification_database_representation_in_english()
    {
        $notifiable = new User();
        $notifiable->language = 'en';

        $founder = User::factory()->make([
            'first_name_en' => 'Jane',
            'last_name_en' => 'Doe',
            'name' => 'Jane Doe',
        ]);

        $sessions = [
            [
                'date' => '2026-07-12',
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]
        ];

        $notification = new MentorshipSessionMentorBookingNotification($founder, $sessions);

        $data = $notification->toDatabase($notifiable);

        $this->assertEquals(
            'You have a new mentorship session with Jane Doe on 2026-07-12 at 10:00 – 11:00 (AM/PM).',
            $data['message']
        );
    }

    public function test_notification_database_representation_in_arabic()
    {
        $notifiable = new User();
        $notifiable->language = 'ar';

        $founder = User::factory()->make([
            'first_name_en' => 'Jane',
            'last_name_en' => 'Doe',
            'first_name_ar' => 'Jane',
            'last_name_ar' => 'Doe',
            'name' => 'Jane Doe',
        ]);

        $sessions = [
            [
                'date' => '2026-07-12',
                'start_time' => '10:00',
                'end_time' => '11:00',
            ]
        ];

        $notification = new MentorshipSessionMentorBookingNotification($founder, $sessions);

        $data = $notification->toDatabase($notifiable);

        $this->assertEquals(
            'لديك جلسة إرشاد جديدة مع Jane Doe بتاريخ 2026-07-12 في 10:00 – 11:00 (AM/PM).',
            $data['message']
        );
    }

    public function test_via_returns_database_channel()
    {
        $notifiable = new User();
        $founder = User::factory()->make();
        $sessions = [];
        
        $notification = new MentorshipSessionMentorBookingNotification($founder, $sessions);

        $channels = $notification->via($notifiable);
        $this->assertContains('database', $channels);
    }
}
