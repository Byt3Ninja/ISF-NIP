<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\User;
use App\Notifications\MentorshipRequestNotification;

class MentorshipRequestNotificationTest extends TestCase
{
    public function test_mentor_receives_mentor_specific_message()
    {
        $mentorUser = $this->createMock(User::class);
        $mentorUser->method('hasActualRole')
            ->with($this->logicalOr($this->equalTo('Mentor'), $this->equalTo('mentor')))
            ->willReturn(true);

        $notificationData = [
            'company_name' => 'Acme Startup',
            'Mentorname' => 'John Doe',
        ];

        $notification = new MentorshipRequestNotification($notificationData);
        $result = $notification->toDatabase($mentorUser);

        $this->assertEquals(
            'Acme Startup has sent you a mentorship request. They are eager to learn from your expertise and guidance.',
            $result['message']
        );
    }

    public function test_founder_does_not_receive_mentor_specific_message()
    {
        $founderUser = $this->createMock(User::class);
        $founderUser->method('hasActualRole')
            ->with($this->logicalOr($this->equalTo('Mentor'), $this->equalTo('mentor')))
            ->willReturn(false);
        $founderUser->method('preferredLocale')
            ->willReturn('en');

        $notificationData = [
            'company_name' => 'Acme Startup',
            'Mentorname' => 'John Doe',
        ];

        $notification = new MentorshipRequestNotification($notificationData);
        $result = $notification->toDatabase($founderUser);

        $this->assertStringNotContainsString('has sent you a mentorship request. They are eager to learn from your expertise', $result['message']);
        $this->assertStringContainsString('Mentorship Session with John Doe has been Approved', $result['message']);
    }
}
