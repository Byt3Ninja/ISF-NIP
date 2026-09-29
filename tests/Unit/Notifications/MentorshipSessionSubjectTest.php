<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\User;
use App\MentorshipRequest;
use App\Notifications\MentorshipSessionFounderNotification;
use App\Notifications\MentorNotifyMentorshipNotification;
use App\Notifications\MentorshipSessionMentor;

class MentorshipSessionSubjectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $connection = \Mockery::mock(\Illuminate\Database\ConnectionInterface::class);
        $grammar = new \Illuminate\Database\Query\Grammars\Grammar();
        $processor = new \Illuminate\Database\Query\Processors\Processor();
        
        $connection->shouldReceive('getName')->andReturn('sqlite');
        $connection->shouldReceive('getQueryGrammar')->andReturn($grammar);
        $connection->shouldReceive('getPostProcessor')->andReturn($processor);
        $connection->shouldReceive('query')->andReturn(new \Illuminate\Database\Query\Builder($connection, $grammar, $processor));
        
        $connection->shouldReceive('select')->andReturn([
            [
                'host' => 'smtp.mailtrap.io',
                'Port' => 2525,
                'encryption' => 'tls',
                'Mail' => 'no-reply@example.com',
                'Mailpassword' => 'password',
                'mail_from_name' => 'Platform Name',
                'mail_from_address' => 'no-reply@example.com',
                'Platform_Name' => 'Platform Name',
            ]
        ]);
        
        $resolver = \Mockery::mock(\Illuminate\Database\ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->andReturn($connection);
        
        \App\Settings::setConnectionResolver($resolver);
        \App\User::setConnectionResolver($resolver);
        \App\MentorshipRequest::setConnectionResolver($resolver);
    }

    /** @test */
    public function it_sends_correct_founder_subject_in_english()
    {
        $founder = new User();
        $founder->language = 'en';
        $founder->email = 'founder@example.com';
        $founder->setRelation('organization', null);

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
    }

    /** @test */
    public function it_sends_correct_founder_subject_in_arabic()
    {
        $founder = new User();
        $founder->language = 'ar';
        $founder->email = 'founder@example.com';
        $founder->setRelation('organization', null);

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
    }

    /** @test */
    public function it_sends_correct_mentor_booking_subject_in_english()
    {
        $mentor = new User();
        $mentor->language = 'en';
        $mentor->email = 'mentor@example.com';
        $mentor->setRelation('organization', null);

        $founder = new User();
        $founder->first_name_en = 'Alice';
        $founder->last_name_en = 'Smith';

        $session = new MentorshipRequest();
        $session->setRelation('user', $founder);
        $session->zoom_date = '2026-07-20 10:00:00';
        $session->period = 60;
        $session->session_type = 'Online';

        $notification = new MentorNotifyMentorshipNotification([], $session);

        app()->setLocale('en');
        $mailMessage = $notification->toMail($mentor);

        $this->assertEquals('Mentorship Session with Alice Smith.', $mailMessage->subject);
    }

    /** @test */
    public function it_sends_correct_mentor_booking_subject_in_arabic()
    {
        $mentor = new User();
        $mentor->language = 'ar';
        $mentor->email = 'mentor@example.com';
        $mentor->setRelation('organization', null);

        $founder = new User();
        $founder->first_name_ar = 'سارة';
        $founder->last_name_ar = 'أحمد';

        $session = new MentorshipRequest();
        $session->setRelation('user', $founder);
        $session->zoom_date = '2026-07-20 10:00:00';
        $session->period = 60;
        $session->session_type = 'Online';

        $notification = new MentorNotifyMentorshipNotification([], $session);

        app()->setLocale('ar');
        $mailMessage = $notification->toMail($mentor);

        $this->assertEquals('جلسة إرشاد مع سارة أحمد.', $mailMessage->subject);
    }

    /** @test */
    public function it_sends_correct_mentor_reschedule_subject_in_english()
    {
        $mentor = new User();
        $mentor->language = 'en';
        $mentor->email = 'mentor@example.com';
        $mentor->setRelation('organization', null);

        $sessionData = [
            'subject' => 'Mentorship Session with Alice Smith.',
            'mentorName' => 'John Doe',
            'startupOwnerName' => 'Alice Smith',
            'startupName' => 'Alice Tech',
            'sessionDate' => '2026-07-20',
            'sessionTime' => '10:00 AM – 11:00 AM (AM/PM)',
            'duration' => '60 min',
            'sessionType' => 'Online',
            'meetingLink' => 'https://zoom.us/j/123456',
            'meetingPass' => '123456',
            'status' => 'Yes',
            'user' => 'mentor',
            'lang' => 'en',
            'is_hackathon' => false,
        ];

        $notification = new MentorshipSessionMentor($sessionData);
        $mailMessage = $notification->toMail($mentor);

        $this->assertEquals('Mentorship Session with Alice Smith.', $mailMessage->subject);
        
        $rendered = $mailMessage->render();
        $this->assertStringContainsString('You have a new session(s) with Alice Smith.', $rendered);
        $this->assertStringContainsString('Founder Name:', $rendered);
        $this->assertStringContainsString('Alice Smith.', $rendered);
        $this->assertStringContainsString('Startup Name:', $rendered);
        $this->assertStringContainsString('Alice Tech.', $rendered);
        $this->assertStringContainsString('Date(s):', $rendered);
        $this->assertStringContainsString('2026-07-20.', $rendered);
        $this->assertStringContainsString('Time(s):', $rendered);
        $this->assertStringContainsString('10:00 AM – 11:00 AM (AM/PM).', $rendered);
        $this->assertStringContainsString('Type:', $rendered);
        $this->assertStringContainsString('Online', $rendered);
        $this->assertStringNotContainsString('Duration:', $rendered);
    }

    /** @test */
    public function it_sends_correct_mentor_reschedule_subject_in_arabic()
    {
        $mentor = new User();
        $mentor->language = 'ar';
        $mentor->email = 'mentor@example.com';
        $mentor->setRelation('organization', null);

        $sessionData = [
            'subject' => 'جلسة إرشاد مع سارة أحمد.',
            'mentorName' => 'John Doe',
            'startupOwnerName' => 'سارة أحمد',
            'startupName' => 'سارة تك',
            'sessionDate' => '2026-07-20',
            'sessionTime' => '10:00 صباحاً – 11:00 صباحاً (AM/PM)',
            'duration' => '60 min',
            'sessionType' => 'Online',
            'meetingLink' => 'https://zoom.us/j/123456',
            'meetingPass' => '123456',
            'status' => 'Yes',
            'user' => 'mentor',
            'lang' => 'ar',
            'is_hackathon' => false,
        ];

        $notification = new MentorshipSessionMentor($sessionData);
        $mailMessage = $notification->toMail($mentor);

        $this->assertEquals('جلسة إرشاد مع سارة أحمد.', $mailMessage->subject);

        $rendered = $mailMessage->render();
        $this->assertStringContainsString('اسم المؤسس:', $rendered);
        $this->assertStringContainsString('سارة أحمد.', $rendered);
        $this->assertStringContainsString('اسم الشركة الناشئة:', $rendered);
        $this->assertStringContainsString('سارة تك.', $rendered);
        $this->assertStringContainsString('التاريخ:', $rendered);
        $this->assertStringContainsString('2026-07-20.', $rendered);
        $this->assertStringContainsString('الوقت:', $rendered);
        $this->assertStringContainsString('10:00 صباحاً – 11:00 صباحاً (AM/PM).', $rendered);
        $this->assertStringContainsString('النوع:', $rendered);
        $this->assertStringNotContainsString('المدة:', $rendered);
    }

    /** @test */
    public function it_hides_startup_name_when_program_is_hackathon()
    {
        $mentor = new User();
        $mentor->language = 'en';
        $mentor->email = 'mentor@example.com';
        $mentor->setRelation('organization', null);

        $sessionData = [
            'subject' => 'Mentorship Session with Alice Smith.',
            'mentorName' => 'John Doe',
            'startupOwnerName' => 'Alice Smith',
            'startupName' => 'Alice Tech',
            'sessionDate' => '2026-07-20',
            'sessionTime' => '10:00 AM – 11:00 AM (AM/PM)',
            'duration' => '60 min',
            'sessionType' => 'Online',
            'meetingLink' => 'https://zoom.us/j/123456',
            'meetingPass' => '123456',
            'status' => 'Yes',
            'user' => 'mentor',
            'lang' => 'en',
            'is_hackathon' => true,
        ];

        $notification = new MentorshipSessionMentor($sessionData);
        $mailMessage = $notification->toMail($mentor);
        
        $rendered = $mailMessage->render();
        $this->assertStringContainsString('Founder Name:', $rendered);
        $this->assertStringNotContainsString('Startup Name:', $rendered);
    }

    /** @test */
    public function it_hides_location_and_meeting_link_when_session_is_declined()
    {
        $mentor = new User();
        $mentor->language = 'en';
        $mentor->email = 'mentor@example.com';
        $mentor->setRelation('organization', null);

        $sessionData = [
            'subject' => 'Mentorship Session with Alice Smith.',
            'mentorName' => 'John Doe',
            'startupOwnerName' => 'Alice Smith',
            'startupName' => 'Alice Tech',
            'sessionDate' => '2026-07-20',
            'sessionTime' => '10:00 AM – 11:00 AM (AM/PM)',
            'duration' => '60 min',
            'sessionType' => 'Online',
            'meetingLink' => 'https://zoom.us/j/123456',
            'meetingPass' => '123456',
            'status' => 'No',
            'user' => 'mentor',
            'lang' => 'en',
            'is_hackathon' => false,
        ];

        $notification = new MentorshipSessionMentor($sessionData);
        $mailMessage = $notification->toMail($mentor);
        
        $rendered = $mailMessage->render();
        $this->assertStringContainsString('The mentorship session request with Alice Smith has been declined.', $rendered);
        $this->assertStringNotContainsString('Join Meeting', $rendered);
        $this->assertStringNotContainsString('Location', $rendered);
    }
}
