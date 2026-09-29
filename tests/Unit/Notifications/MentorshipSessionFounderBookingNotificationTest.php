<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\User;
use App\Notifications\MentorshipSessionFounderBookingNotification;

class MentorshipSessionFounderBookingNotificationTest extends TestCase
{
    public function test_notification_database_representation_in_english()
    {
        $notifiable = new User();
        $notifiable->language = 'en';

        $notification = new MentorshipSessionFounderBookingNotification(
            'John Doe',
            '2026-07-12',
            '10:00',
            '11:00'
        );

        $data = $notification->toDatabase($notifiable);

        $this->assertEquals(
            'You have a new mentorship session with John Doe on 2026-07-12 at 10:00 – 11:00 (AM/PM).',
            $data['message']
        );
    }

    public function test_notification_database_representation_in_arabic()
    {
        $notifiable = new User();
        $notifiable->language = 'ar';

        $notification = new MentorshipSessionFounderBookingNotification(
            'John Doe',
            '2026-07-12',
            '10:00',
            '11:00'
        );

        $data = $notification->toDatabase($notifiable);

        $this->assertEquals(
            'لديك جلسة إرشاد جديدة مع John Doe بتاريخ 2026-07-12 في 10:00 – 11:00 (AM/PM).',
            $data['message']
        );
    }

    public function test_via_returns_database_channel()
    {
        $notifiable = new User();
        $notification = new MentorshipSessionFounderBookingNotification(
            'John Doe',
            '2026-07-12',
            '10:00',
            '11:00'
        );

        $channels = $notification->via($notifiable);
        $this->assertEquals(['database'], $channels);
    }
}
