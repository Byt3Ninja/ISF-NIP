<?php

namespace App\Jobs;

use App\InviteMentor;
use Illuminate\Bus\Queueable;
use App\Mail\InviteMentorMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendInviteMentorEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
}
