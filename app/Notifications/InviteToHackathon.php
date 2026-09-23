<?php

namespace App\Notifications;

use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Throwable;

class InviteToHackathon extends Notification implements ShouldQueue
{
  use Queueable, ConfiguresSmtp;

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

    /**
     * Team leader.
     *
     * @var mixed
     */
    public $teamLeader;

    /**
     * Team name.
     *
     * @var string
     */
    public $team_q;

    /**
     * Team member.
     *
     * @var mixed
     */
    public $teamMember;

    /**
     * Team member row id (for marking the invite failed on worker failure).
     *
     * @var int|null
     */
    public $teamMemberId;

    public function __construct($teamLeader, $team_q, $teamMember)
    {
        $this->teamLeader = $teamLeader;
        $this->team_q = $team_q;
        $this->teamMember = $teamMember;
        $this->teamMemberId = $teamMember->id ?? null;
    }

    /**
     * Resolve the organization for SMTP/branding without depending on
     * user-only properties. Preserves prior behavior for real users
     * (their own organization) and falls back to the team leader / cycle
     * for AnonymousNotifiable email recipients.
     */
    private function resolveOrganization(object $notifiable)
    {
        if ($notifiable instanceof \App\User) {
            return $notifiable->organization;
        }
        return $this->teamLeader->organization
            ?? optional($this->team_q->cycle)->organization;
    }

 /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $isUser = $notifiable instanceof \App\User;

        $org = $this->resolveOrganization($notifiable);
        $smtpSettings = $org?->organizationSettings;

        // Configure SMTP if settings exist
        $this->configureSmtp($smtpSettings);

        // Organization channel toggles (default to true when not configured)
        $orgAcceptEmail = $smtpSettings?->accept_email ?? true;
        $orgAcceptWhatsapp = $smtpSettings?->accept_whatsapp ?? true;
        $orgAcceptPusher = $smtpSettings?->accept_pusher ?? true;

        // User-level prefs only apply to real users; an email-only recipient
        // (AnonymousNotifiable) can receive mail only.
        $userAcceptWhatsapp = $isUser ? ($notifiable->accept_whatsapp ?? true) : false;
        $userAcceptPusher   = $isUser ? ($notifiable->accept_pusher ?? true) : false;
        $userAcceptEmail    = $isUser ? ($notifiable->accept_email ?? true) : true;

        $channels = [];

        if (GlobalNotificationSettingsService::isWhatsAppEnabled()
            && $orgAcceptWhatsapp
            && $userAcceptWhatsapp) {
            $channels[] = WhatsAppChannel::class;
        }

        if (GlobalNotificationSettingsService::isPusherEnabled()
            && $orgAcceptPusher
            && $userAcceptPusher) {
            $channels[] = 'broadcast';
        }

        if (GlobalNotificationSettingsService::isEmailEnabled()
            && $orgAcceptEmail
            && $userAcceptEmail) {
            $channels[] = 'mail';
        }

        // Store in database only for real users (AnonymousNotifiable cannot).
        if (!empty($channels) && $isUser) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $org = $this->resolveOrganization($notifiable);
        $smtpSettings = $org?->organizationSettings;

        // Get from address and name
        $from = $this->getFromAddress($smtpSettings);
        // return (new MailMessage)
        //     ->subject('Invitation to Join a Hackathon Team')
        //     ->greeting('Hello ' . $notifiable->first_name)
        //     ->line("You have been invited to join the team: {$this->teamName}")
        //     ->line("Invited by: {$this->teamLeader->first_name} {$this->teamLeader->last_name}")
        //     ->action('Accept Invitation', url('/invitations/accept')) // عدّل الرابط حسب مشروعك
        //     ->line('Thank you for using our platform!');

        $orgData = $this->buildEmailOrganizationData($org);

            return (new MailMessage)
            ->subject(__('admin.Invitation_to_Join_a_Hackathon_Team'))
            ->markdown('emails.hackathon_inviteTeam', array_merge([
                'data' => $this->teamLeader,
                'teamName' => $this->team_q,
                'teamMember' => $this->teamMember,
                // The recipient can be an AnonymousNotifiable (email-only invite
                // with no registered user), so these may legitimately be blank.
                'recipientName' => $this->displayName($notifiable),
                'recipientGender' => $notifiable->gender ?? null,
                'leaderName' => $this->displayName($this->teamLeader),
            ], $orgData));
    }

    /**
     * Human-readable name for a user in the current locale, falling back to the
     * other locale and then the email. Returns '' for an AnonymousNotifiable,
     * which has no name columns at all.
     */
    private function displayName($user): string
    {
        if (!$user) {
            return '';
        }

        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $other = $locale === 'ar' ? 'en' : 'ar';

        $first = $user->{'first_name_'.$locale} ?? null;
        $last = $user->{'last_name_'.$locale} ?? null;
        $first = $first ?: ($user->{'first_name_'.$other} ?? null);
        $last = $last ?: ($user->{'last_name_'.$other} ?? null);

        return trim($first.' '.$last);
    }

    /**
     * WhatsApp message.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.hackathon_invite.whatsapp', [
            'leader' => ($this->teamLeader->first_name_en ?? '').' '.($this->teamLeader->last_name_en ?? ''),
            'team' => $this->team_q->name ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.Invitation_to_Join_a_Hackathon_Team'),
            'message' => __('notifications.hackathon_invite.broadcast', ['team' => $this->team_q->name ?? '']),
            'time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Database (stored) representation. Required because via() adds the
     * 'database' channel for registered users; without this the queue job
     * throws "Notification is missing toDatabase / toArray method".
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'subject' => __('admin.Invitation_to_Join_a_Hackathon_Team'),
            'message' => __('notifications.hackathon_invite.broadcast', ['team' => $this->team_q->name ?? '']),
            'team'    => $this->team_q->id ?? null,
            'leader'  => $this->teamLeader->id ?? null,
            'member'  => $this->teamMember->id ?? null,
        ];
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        if ($this->teamMemberId) {
            \App\Team_member::where('id', $this->teamMemberId)
                ->where('acceptInvition', 'pending')
                ->update(['acceptInvition' => 'failed']);
        }

        Log::error('InviteToHackathon notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'team' => $this->team_q,
        ]);
    }
}
