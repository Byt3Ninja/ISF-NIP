<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\ServiceMessageEmailNotification;

class SendServiceMessageEmailReminders extends Command
{
    protected $signature = 'service-messages:send-email-reminders';

    protected $description = 'Send delayed email notifications for unread service request messages';

    public function handle(): void
    {
        if (in_array(config('app.env'), ['local'])) {
            $this->info('Skipped: local environment.');
            return;
        }

        $now = Carbon::now();

        // Find unread, un-emailed messages grouped by thread + sender direction
        $unreadThreads = DB::table('service_request_messages as m')
            ->join('services_requests as sr', 'm.service_request_id', '=', 'sr.id')
            ->join('services as s', 'sr.service_id', '=', 's.id')
            ->leftJoin('service_providers as sp', 's.service_provider_id', '=', 'sp.id')
            ->whereNull('m.read_at')
            ->whereNull('m.emailed_at')
            ->whereNull('sr.deleted_at')
            ->whereNull('s.deleted_at')
            ->select([
                'sr.id as service_request_id',
                'sr.user_id as founder_id',
                'sr.service_id',
                'sp.user_id as provider_user_id',
                'm.sender_id',
                DB::raw('COUNT(*) as unread_count'),
                DB::raw('MIN(m.created_at) as oldest_unread_at'),
            ])
            ->groupBy('sr.id', 'sr.user_id', 'sr.service_id', 'sp.user_id', 'm.sender_id')
            ->get();

        $sentCount = 0;

        foreach ($unreadThreads as $thread) {
            $senderIsFounder = ((int) $thread->sender_id === (int) $thread->founder_id);
            $delayMinutes    = $senderIsFounder ? 60 : 1440; // 1 hour or 24 hours

            $oldestUnread = Carbon::parse($thread->oldest_unread_at);
            if ($now->diffInMinutes($oldestUnread) < $delayMinutes) {
                continue;
            }

            $recipientId = $senderIsFounder
                ? (int) ($thread->provider_user_id ?? 0)
                : (int) $thread->founder_id;

            if (!$recipientId) continue;

            $recipient = User::find($recipientId);
            if (!$recipient) continue;

            $this->sendEmail($recipient, $thread);

            // Mark these messages as emailed
            DB::table('service_request_messages')
                ->where('service_request_id', $thread->service_request_id)
                ->where('sender_id', $thread->sender_id)
                ->whereNull('read_at')
                ->whereNull('emailed_at')
                ->update(['emailed_at' => $now]);

            $sentCount++;
        }

        $this->info("Processed {$sentCount} email reminder(s).");
    }

    private function sendEmail(User $recipient, $thread): void
    {
        try {
            $recipientLang = $recipient->language ?? 'en';

            $service = DB::table('services')
                ->where('id', $thread->service_id)
                ->select(['name_en', 'name_ar'])
                ->first();

            $serviceName = $recipientLang === 'ar'
                ? ($service->name_ar ?: $service->name_en)
                : $service->name_en;

            $sender     = User::find($thread->sender_id);
            $senderName = $sender
                ? trim(($sender->{"first_name_{$recipientLang}"} ?? $sender->first_name_en) . ' ' . ($sender->{"last_name_{$recipientLang}"} ?? $sender->last_name_en))
                : '';

            $timestamp = Carbon::parse($thread->oldest_unread_at)->format('M d, H:i');

            $senderIsFounder = ((int) $thread->sender_id === (int) $thread->founder_id);
            $actionUrl = $senderIsFounder
                ? '/dashboard/requests/all?open_request=' . $thread->service_request_id . '&tab=messages'
                : '/dashboard/requests/mine?open_request=' . $thread->service_request_id . '&tab=messages';

            $recipientFirstName = $recipient->{"first_name_{$recipientLang}"} ?? $recipient->first_name_en;
            $recipientLastName  = $recipient->{"last_name_{$recipientLang}"} ?? $recipient->last_name_en;

            $params = [
                'sender_name'  => $senderName,
                'timestamp'    => $timestamp,
                'service_name' => $serviceName,
            ];

            $recipient->notify(new ServiceMessageEmailNotification([
                'subject'    => __('admin.service_message_email_subject', $params, $recipientLang),
                'greeting'   => __('admin.service_message_email_greeting', [
                    'recipient_name' => trim($recipientFirstName . ' ' . $recipientLastName),
                ], $recipientLang),
                'body'       => __('admin.service_message_email_body', $params, $recipientLang),
                'cta_text'   => __('admin.service_message_view_cta', [], $recipientLang),
                'action_url' => $actionUrl,
            ]));
        } catch (\Throwable $th) {
            Log::error('Service message email reminder failed', [
                'error'      => $th->getMessage(),
                'recipient'  => $recipient->id,
                'request_id' => $thread->service_request_id,
            ]);
        }
    }
}
