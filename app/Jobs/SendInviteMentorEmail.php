<?php

namespace App\Jobs;

use App\InviteMentor;
use App\Support\ReleasesNotificationClaim;
use Illuminate\Bus\Queueable;
use Throwable;
use App\Mail\InviteMentorMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendInviteMentorEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ReleasesNotificationClaim;

    /**
     * Create a new job instance.
     */
   public function __construct(public InviteMentor $invitation, public ?string $ccEmail = null) {}

    public function handle()
    {
        
        Mail::to($this->invitation->email)
            ->cc($this->ccEmail)
            ->send(new InviteMentorMail($this->invitation));
    }

    /** When the worker gives up, free email_sent_at so the invitation can be sent again. */
    public function failed(Throwable $exception): void
    {
        $this->releaseClaim();
        report($exception);
    }
}
