<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\InviteMentor;
use App\Organization;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;


class InviteMentorMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

   public InviteMentor $invitation;

    public function __construct(InviteMentor $invitation)
    {
        $this->invitation = $invitation;
    }

    public function build()
    {
        $orgIds = $this->invitation->organization_ids ?? [];
        $firstOrg = !empty($orgIds) ? Organization::find($orgIds[0]) : null;
        $orgData = $this->buildEmailOrganizationData($firstOrg);

        $mail = $this->subject($this->invitation->subject)
                     ->view('emails.invite_mentor')
                     ->with(array_merge([
                         'name'        => $this->invitation->name,
                         'messageBody' => $this->invitation->message_content,
                         'scope'       => $this->invitation->scope,
                         'organizations' => Organization::whereIn('id', $orgIds)->get()->map(fn($org) => ['id' => $org->id, 'name' => $org->name_en])->toArray(),
                     ], $orgData));

        // Attach files if exist
        $attachments = $this->invitation->attachments;

if (is_string($attachments)) {
    $attachments = json_decode($attachments, true) ?? [];
}

if (!empty($attachments) && is_array($attachments)) {

    $disk = Storage::disk('UsersDiskInviteMentor');

    foreach ($attachments as $file) {
        // استخراج اسم الملف فقط من أي format (URL كامل أو relative path أو اسم الملف)
        $file = basename($file);

        if ($disk->exists($file)) {
            $mail->attach($disk->path($file));
        } else {
            logger()->error('InviteMentor attachment not found on UsersDiskInviteMentor', [
                'file' => $file,
                'disk_root' => config('filesystems.disks.UsersDiskInviteMentor.root'),
            ]);
        }
    }
}

        return $mail;
    }
}
