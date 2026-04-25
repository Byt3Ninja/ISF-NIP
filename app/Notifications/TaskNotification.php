<?php

namespace App\Notifications;

use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use Throwable;

class TaskNotification extends Notification implements ShouldQueue
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
     * Notification data.
     *
     * @var array
     */
    protected $data;
    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;
        
        // Configure SMTP if settings exist
        $this->configureSmtp($smtpSettings);
        
        // Organization channel toggles (default to true when not configured)
        $orgAcceptEmail = $smtpSettings?->accept_email ?? true;
        $orgAcceptWhatsapp = $smtpSettings?->accept_whatsapp ?? true;
        $orgAcceptPusher = $smtpSettings?->accept_pusher ?? true;

        $channels = [];

        // WhatsApp if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isWhatsAppEnabled() 
            && $orgAcceptWhatsapp 
            && $notifiable->accept_whatsapp) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled() 
            && $orgAcceptPusher 
            && $notifiable->accept_pusher) {
            $channels[] = 'broadcast';
        }

        // Email if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isEmailEnabled() 
            && $orgAcceptEmail 
            && $notifiable->accept_email) {
            $channels[] = 'mail';
        }

        // Always store in database for audit/history if any channels are active
        if (!empty($channels)) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;
        
        // Get from address and name
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        return (new MailMessage)
            ->subject($this->getSubject())
            ->markdown('emails.mentor-task', array_merge([
                'data' => $this->data,
                'notifiable' => $notifiable,
                'action' => $this->data['action']
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            // 'task_id' => $this->data['task']->id,
            // 'title' => $this->data['task']->title,
            'message' => $this->data['message'],
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.task.whatsapp', [
            'message' => $this->data['message'] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->getSubject(),
            'message' => __('notifications.task.broadcast', ['message' => $this->data['message'] ?? '']),
            'time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $mentorName = $this->data['mentor']->name ?? null;
        $taskTitle  = $this->data['task']->title;
        $isAr       = app()->getLocale() === 'ar';
        $action     = $this->data['action'] ?? 'add';

        if ($notifiable->hasRole('registered')) {
            $message = match($action) {
                'add'     => $isAr ? "تم إضافة مهمة جديدة: {$taskTitle} - بواسطة المينتور: {$mentorName}"   : "New task added: {$taskTitle} - By mentor: {$mentorName}",
                'update'  => $isAr ? "تم تحديث المهمة: {$taskTitle} - بواسطة المينتور: {$mentorName}"        : "Task updated: {$taskTitle} - By mentor: {$mentorName}",
                'archive' => $isAr ? "تمت أرشفة المهمة: {$taskTitle} - بواسطة المينتور: {$mentorName}"       : "Task archived: {$taskTitle} - By mentor: {$mentorName}",
                'reopen'  => $isAr ? "تمت إعادة فتح المهمة: {$taskTitle} - بواسطة المينتور: {$mentorName}"   : "Task reopened: {$taskTitle} - By mentor: {$mentorName}",
                default   => $isAr ? "تحديث على المهمة: {$taskTitle}" : "Task update: {$taskTitle}",
            };
        } else {
            $startupList = implode('، ', $this->data['startupNames'] ?? []);
            $message = match($action) {
                'add'     => $isAr ? "تم إضافة المهمة: {$taskTitle} إلى: {$startupList}"           : "Task added: {$taskTitle} to: {$startupList}",
                'update'  => $isAr ? "تم تحديث المهمة: {$taskTitle} للشركات: {$startupList}"        : "Task updated: {$taskTitle} for: {$startupList}",
                'archive' => $isAr ? "تمت أرشفة المهمة: {$taskTitle} - الشركات: {$startupList}"     : "Task archived: {$taskTitle} - Startups: {$startupList}",
                'reopen'  => $isAr ? "تمت إعادة فتح المهمة: {$taskTitle} - الشركات: {$startupList}" : "Task reopened: {$taskTitle} - Startups: {$startupList}",
                default   => $isAr ? "تحديث على المهمة: {$taskTitle}" : "Task update: {$taskTitle}",
            };
        }

        return [
            'task_id' => $this->data['task']->id,
            'title'   => $taskTitle,
            'message' => $message,
        ];
    }

    private function getSubject(): string
    {
        $action = $this->data['action'] ?? 'add';
        $key = "notifications.task.subject_{$action}";
        $translated = __($key);
        return $translated !== $key ? $translated : __('notifications.task.subject');
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('TaskNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'data' => $this->data,
        ]);
    }
}
