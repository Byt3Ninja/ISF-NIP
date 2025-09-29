<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $filePath,
        public string $fileName
    ) {
    }

    public function via(object $notifiable): array
    {
        // Export is mail-only; use AnonymousNotifiable when needed
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.export.subject'))
            ->view('admin.emails.email')
            ->attach($this->filePath, [
                'as' => $this->fileName,
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}
