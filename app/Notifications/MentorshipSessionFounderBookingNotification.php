<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class MentorshipSessionFounderBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $mentorName;
    public $sessionDate;
    public $startTime;
    public $endTime;

    /**
     * Create a new notification instance.
     */
    public function __construct($mentorName, $sessionDate, $startTime, $endTime)
    {
        $this->mentorName = $mentorName;
        $this->sessionDate = $sessionDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $this->locale($notifiable->language ?: 'en');

        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $oldLocale = app()->getLocale();
        $locale = $notifiable->language ?: 'en';
        app()->setLocale($locale);

        $message = __('notifications.mentorship_session.founder_booking', [
            'mentor_name' => $this->mentorName,
            'session_date' => $this->sessionDate,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
        ]);

        app()->setLocale($oldLocale);

        return [
            'message' => $message
        ];
    }
}
