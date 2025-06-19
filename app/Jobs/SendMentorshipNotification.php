<?php

namespace App\Jobs;

use App\User;
use App\MentorshipRequest;
use App\Notifications\TemplateEmail;
use App\Events\MentorshipSessionUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class SendMentorshipNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sessionId;
    protected $status;

    /**
     * Create a new job instance.
     */
    public function __construct($sessionId, $status)
    {
        $this->sessionId = $sessionId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $session = MentorshipRequest::findOrFail($this->sessionId);
        $mentor = User::findOrFail($session->mentor_id);
        $user = User::findOrFail($session->user_id);
        $locale = App::getLocale() ?? 'en';

        // Log job execution
        Log::info('Handling SendMentorshipNotification', [
            'session_id' => $this->sessionId,
            'status' => $this->status,
            'locale' => $locale
        ]);

        // Notify Mentor
        $this->sendNotification(
            $mentor,
            $user->name,
            __("messages.dear", ['name' => $mentor->name], $locale)
        );

        // Notify User
        $this->sendNotification(
            $user,
            $mentor->name,
            __("messages.dear", ['name' => $user->name], $locale)
        );

        // Broadcast event
        broadcast(new MentorshipSessionUpdated($mentor->id, $user->id, $this->createMessage($user->name, $locale), $this->status));
    }

    /**
     * Send TemplateEmail notification.
     */
    protected function sendNotification($recipient, $otherPartyName, $greeting)
    {
        $locale = App::getLocale() ?? 'en';
        App::setLocale($locale);
        $messageContent = $this->createMessage($otherPartyName, $locale);

        $msg = new TemplateEmail([
            __("messages.mentorship_session_subject", [], $locale),
            $greeting,
            $messageContent['email'],
            "/dashboard"
        ]);

        if (!in_array(env('APP_ENV', 'local'), ['local'])) {
            try {
                $recipient->notify($msg);
            } catch (\Throwable $th) {
                Log::error('Notification failed for user: ' . $recipient->id, ['error' => $th->getMessage()]);
            }
        }
    }

    /**
     * Create message content for email.
     *
     * @param string $otherPartyName
     * @param string $locale
     * @return array
     */
    protected function createMessage($otherPartyName, $locale = 'en')
    {
        App::setLocale($locale);
        if ($this->status == 'Yes') {
            return [
                'email' => __("messages.approved_message", [], $locale)
            ];
        }

        return [
            'email' => __("messages.denied_message", [], $locale)
        ];
    }
}