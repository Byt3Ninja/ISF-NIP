<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\User;
use App\Notifications\MentorshipRequestNotification;

class MentorshipRequestNotificationTest extends TestCase
{
    public function test_mentor_receives_mentor_specific_message()
    {
        $mentorUser = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRole', 'hasActualRole'])
            ->getMock();

        $mentorUser->method('hasActualRole')
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
        $founderUser = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRole', 'hasActualRole', 'preferredLocale'])
            ->getMock();

        $founderUser->method('hasActualRole')
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
