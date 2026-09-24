<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\TimezoneService;

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

        // GV-9566: platform wall-clock, shown in the founder's own timezone.
        $start = TimezoneService::wallClockForViewer($this->sessionDate, $this->startTime, $notifiable);
        $end = TimezoneService::wallClockForViewer($this->sessionDate, $this->endTime, $notifiable);

        $message = __('notifications.mentorship_session.founder_booking', [
            'mentor_name' => $this->mentorName,
            'session_date' => $start['date'],
            'start_time' => $start['time'],
            'end_time' => $end['time'],
        ]);

        app()->setLocale($oldLocale);

        return [
            'message' => $message
        ];
    }
}
