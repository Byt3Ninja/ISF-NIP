<?php

namespace App\Notifications;

use App\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

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

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('UserInvitationNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'invitation_id' => $this->invitation->id ?? null,
        ]);
    }
}
