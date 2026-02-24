<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;
use Illuminate\Support\Facades\Log;

class ServiceMessageEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = 60;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->data['subject'])
            ->greeting($this->data['greeting'])
            ->level('info')
            ->line($this->data['body'])
            ->action($this->data['cta_text'], url($this->data['action_url']))
            ->salutation('Kind regards,');
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ServiceMessageEmailNotification failed', [
            'error' => $exception->getMessage(),
            'data'  => $this->data,
        ]);
    }
}
