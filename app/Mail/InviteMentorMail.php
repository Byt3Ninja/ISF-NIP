<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\InviteMentor;
use App\Organization;
use App\Settings;
use App\Services\RichTextSanitizer;
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

    /**
     * Platform-branded email: the platform logo and name, never an
     * organization's (EmailBranding).
     */
    public function emailBranding()
    {
        return null;
    }

    public function build()
    {
        $orgIds = $this->invitation->organization_ids ?? [];

        // The invitation email always carries the platform's own branding
        // (logo, name) -- never an organization's -- regardless of which
        // organizations the invite is scoped to.
        $orgData = $this->emailBrandingData();

        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        app()->setLocale($locale);

        $platformName = $orgData['org_name'] ?? config('app.name');

        $defaultSubject = $locale === 'ar'
            ? "تمت دعوتك للانضمام إلى {$platformName} كمرشد"
            : "You're Invited to Join {$platformName} as a Mentor";

        // An admin-authored subject/message is optional; when provided it
        // replaces the platform's default template text entirely.
        $subject = $this->invitation->subject ?: $defaultSubject;

        $organizations = Organization::whereIn('id', $orgIds)->get()->map(fn($org) => ['id' => $org->id, 'name' => $org->name_en])->toArray();

        $mail = $this->subject($subject)
                     ->view('emails.invite_mentor')
                     ->with(array_merge([
                         'name'          => $this->invitation->name,
                         'messageBody'   => app(RichTextSanitizer::class)->clean($this->invitation->message_content),
                         'scope'         => $this->invitation->scope,
                         'organizations' => $organizations,
                         // Named distinctly from 'platformName': a global view
                         // composer (AppServiceProvider) sets that key on every
                         // view and would silently override this value.
                         'invitePlatformName' => $platformName,
                         'locale'        => $locale,
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
