<?php

namespace App\Notifications;

use App\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Invitation $invitation)
    {
    }

    public function via(object $notifiable): array
    {
        // This invitation targets an external email (may not be a registered user),
        // so we send mail-only via Notification::route('mail', ...)
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.user_invitation.subject'))
            ->view('emails.invitation', [
                'invitation' => $this->invitation,
            ]);
    }
}
