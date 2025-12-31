<?php

namespace App\Mail;

use App\InviteMentor;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class InviteMentorMail extends Mailable
{
    use SerializesModels;

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

        // Note: Attachments are stored as URLs and will be accessible via links in the email template
        // Physical file attachments would need to be handled differently if required

        return $mail;
    }
}
