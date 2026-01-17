<?php

namespace App\Mail;

use App\InviteMentor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;


class InviteMentorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

   public InviteMentor $invitation;

    public function __construct(InviteMentor $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        $mail = $this->subject($this->invitation->subject)
                     ->view('emails.invite_mentor')
                     ->with([
                         'name'        => $this->invitation->name,
                         'messageBody' => $this->invitation->message_content,
                         'scope'       => $this->invitation->scope,
                         'organizations' => $this->invitation->organization_ids,
                     ]);

        // Attach files if exist
        $attachments = $this->invitation->attachments;
        if (is_string($attachments)) {
            $attachments = json_decode($attachments, true) ?? [];
        }

        if (!empty($attachments) && is_array($attachments)) {
            foreach ($attachments as $file) {
                $mail->attach(storage_path('app/' . $file));
            }
        }

        return $mail;
    }
}
