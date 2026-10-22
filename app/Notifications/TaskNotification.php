<?php

namespace App\Notifications;

use Carbon\Carbon;
use App\MentorshipTask;
use App\ProgramTask;
use App\Services\FileManagerService;
use App\Services\GlobalNotificationSettingsService;
use App\Settings;
use App\TodoList;
use App\Traits\ConfiguresSmtp;
use App\Traits\ResolvesMentorshipOrganization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use Throwable;
use App\Services\ProtectedFiles\ProtectedFiles;
use App\Enums\ProtectedFileSet;

class TaskNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp, ResolvesMentorshipOrganization;

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
        $taskId = $this->data['task']->id ?? null;
        $action = $this->data['action'] ?? 'add';
        if ($taskId && $action !== 'feedback') {
            try {
                $dedupKey = "task_notif_{$taskId}_{$action}_{$notifiable->id}";
                if (!Cache::add($dedupKey, 1, now()->addHour())) {
                    return [];
                }
            } catch (\Throwable $e) {
                Log::warning('TaskNotification dedup cache failed', ['error' => $e->getMessage()]);
            }
        }

        $org = $this->taskOrganization($notifiable);
        $smtpSettings = $org?->organizationSettings;

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
        $org          = $this->taskOrganization($notifiable);
        $smtpSettings = $org?->organizationSettings;

        $this->configureSmtp($smtpSettings);

        $from    = $this->getFromAddress($smtpSettings);
        $orgData = $this->emailBrandingData();

        $action = $this->data['action'] ?? 'add';
        if ($action === 'add' && $notifiable->hasRole('registered')) {
            return $this->toFounderAssignedMail($notifiable, $from, $orgData);
        }
        if ($action === 'archive' && $notifiable->hasRole('registered')) {
            return $this->toFounderArchivedMail($notifiable, $from, $orgData);
        }
        if ($action === 'reopen' && $notifiable->hasRole('registered')) {
            return $this->toFounderReopenedMail($notifiable, $from, $orgData);
        }

        App::setLocale($notifiable->language ?? 'en');

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject($this->getSubject())
            ->markdown('emails.mentor-task', array_merge([
                'data'       => $this->data,
                'notifiable' => $notifiable,
                'action'     => $this->data['action'],
            ], $orgData));
    }

    /**
     * Founder "task assigned" email: platform default language, and a
     * View Task link that resolves to the right page for the task's type
     * (admin-created ProgramTask vs mentor-created MentorshipTask).
     */
    protected function toFounderAssignedMail($notifiable, array $from, array $orgData): MailMessage
    {
        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');

        $task = $this->data['task'];
        $orgName = $orgData['org_name'] ?? $platformName;

        $firstName = $locale === 'ar' ? trim((string) $notifiable->first_name_ar) : '';
        $lastName = $locale === 'ar' ? trim((string) $notifiable->last_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $notifiable->first_name_en);
            $lastName = trim((string) $notifiable->last_name_en);
        }

        $viewUrl = $task instanceof ProgramTask
            ? route('tasks.show', $task->id)
            : route('startup.startup.mentorshipTasks.show', $task->id);

        $dueDate = $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : '';

        $fileManager = app(FileManagerService::class);
        if ($task instanceof ProgramTask) {
            $attachments = collect($task->attachments ?? [])
                ->filter(fn ($a) => ($a['status'] ?? 'attachment') !== 'submission')
                ->map(fn ($a) => [
                    'name' => $a['name'] ?? basename($a['path'] ?? ''),
                    // the authorized file route (the recipient logs in and must be allowed to open it)
                    'url'  => app(ProtectedFiles::class)->urlFor(ProtectedFileSet::TASK_ATTACHMENT, $a['path'] ?? null),
                ])
                ->values()->all();
        } else {
            $attachments = $task->files()->get()->map(fn ($f) => [
                'name' => $f->file_name ?: basename($f->file),
                'url'  => $fileManager->getUrl($f->file, 'mentorship_tasks'),
            ])->all();
        }

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(__('email.task_assigned_email.subject', [], $locale))
            ->view('emails.task_assigned_founder', array_merge([
                'taskLocale' => $locale,
                'taskRecipientName' => trim($firstName . ' ' . $lastName),
                'taskOrgName' => $orgName,
                'taskTitle' => $task->title ?? '',
                'taskDueDate' => $dueDate,
                'taskDescription' => trim((string) ($task->description ?? '')),
                'taskViewUrl' => $viewUrl,
                'taskAttachments' => $attachments,
            ], $orgData));
    }

    /**
     * Founder "task archived" email: platform default language, and a
     * View Task link that opens the archived-tasks board for the task's
     * type (admin-created ProgramTask vs mentor-created MentorshipTask).
     */
    protected function toFounderArchivedMail($notifiable, array $from, array $orgData): MailMessage
    {
        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');

        $task = $this->data['task'];
        $orgName = $orgData['org_name'] ?? $platformName;

        $firstName = $locale === 'ar' ? trim((string) $notifiable->first_name_ar) : '';
        $lastName = $locale === 'ar' ? trim((string) $notifiable->last_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $notifiable->first_name_en);
            $lastName = trim((string) $notifiable->last_name_en);
        }

        $viewUrl = $task instanceof ProgramTask
            ? route('tasks.index')
            : route('startup.mentorship_tasks.dashboard.view');

        $programName = $task instanceof ProgramTask
            ? ($task->program?->title ?? '')
            : ($task->mentorshipRequest?->cycle?->title ?? '');

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(__('email.task_archived_email.subject', ['title' => $task->title ?? ''], $locale))
            ->view('emails.task_archived_founder', array_merge([
                'taskLocale' => $locale,
                'taskRecipientName' => trim($firstName . ' ' . $lastName),
                'taskOrgName' => $orgName,
                'taskTitle' => $task->title ?? '',
                'taskDescription' => trim((string) ($task->description ?? '')),
                'taskProgramName' => $programName,
                'taskViewUrl' => $viewUrl,
            ], $orgData));
    }

    /**
     * Founder "task reopened" email: platform default language, and a
     * View Task link that opens the founder's task board for the task's
     * type (admin-created ProgramTask vs mentor-created MentorshipTask).
     */
    protected function toFounderReopenedMail($notifiable, array $from, array $orgData): MailMessage
    {
        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');

        $task = $this->data['task'];
        $orgName = $orgData['org_name'] ?? $platformName;

        $firstName = $locale === 'ar' ? trim((string) $notifiable->first_name_ar) : '';
        $lastName = $locale === 'ar' ? trim((string) $notifiable->last_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $notifiable->first_name_en);
            $lastName = trim((string) $notifiable->last_name_en);
        }

        $viewUrl = $task instanceof ProgramTask
            ? route('tasks.index')
            : route('startup.mentorship_tasks.dashboard.view');

        $programName = $task instanceof ProgramTask
            ? ($task->program?->title ?? '')
            : ($task->mentorshipRequest?->cycle?->title ?? '');

        $dueDate = $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : '';

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(__('email.task_reopened_email.subject', ['title' => $task->title ?? ''], $locale))
            ->view('emails.task_reopened_founder', array_merge([
                'taskLocale' => $locale,
                'taskRecipientName' => trim($firstName . ' ' . $lastName),
                'taskOrgName' => $orgName,
                'taskTitle' => $task->title ?? '',
                'taskDescription' => trim((string) ($task->description ?? '')),
                'taskProgramName' => $programName,
                'taskDueDate' => $dueDate,
                'taskViewUrl' => $viewUrl,
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        App::setLocale($notifiable->language ?? 'en');

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
        App::setLocale($notifiable->language ?? 'en');

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
        App::setLocale($notifiable->language ?? 'en');

        $mentorName = $this->data['mentor']->name ?? null;
        $taskTitle  = $this->data['task']->title;
        $isAr       = app()->getLocale() === 'ar';
        $action     = $this->data['action'] ?? 'add';
        $programName = $this->data['program_name'] ?? null;

        $message = $isAr ? "تم تحديث المهمة: {$taskTitle}" : "Task update: {$taskTitle}";

        if ($notifiable->hasRole('registered')) {
            $message = match($action) {
                'add'      => $isAr ? "تم إسناد مهمة جديدة إليك في برنامج : {$programName}"  : "You have been assigned a new task in program: {$programName}",
                'update'   => $isAr ? "تم تحديث مهمة مسندة إليك: {$taskTitle}"      : "A task assigned to you has been updated: {$taskTitle}",
                'archive'  => $isAr ? "تمت أرشفة مهمة مسندة إليك: {$taskTitle}"     : "A task assigned to you has been archived: {$taskTitle}",
                'reopen'   => $isAr ? "تمت إعادة فتح المهمة: {$taskTitle}"           : "Task reopened: {$taskTitle}",
                'feedback' => $isAr
                    ? ($this->data['decision'] === 'pass'
                        ? "تم اجتياز {$taskTitle}."
                        : "مطلوب تعديلات على {$taskTitle}.")
                    : ($this->data['decision'] === 'pass'
                        ? "{$taskTitle} passed."
                        : "Revisions requested for : {$taskTitle}."),
                default    => $isAr ? "تم تحديث المهمة: {$taskTitle}" : "Task update: {$taskTitle}",
            };
        } 
       // else {
         //   $startupList = implode('، ', $this->data['startupNames'] ?? []);
           // $message = match($action) {
             //   'add'     => $isAr ? "تم إضافة المهمة: {$taskTitle} إلى: {$startupList}"           : "Task added: {$taskTitle} to: {$startupList}",
               // 'update'  => $isAr ? "تم تحديث المهمة: {$taskTitle} للشركات: {$startupList}"        : "Task updated: {$taskTitle} for: {$startupList}",
               // 'archive' => $isAr ? "تمت أرشفة المهمة: {$taskTitle} - الشركات: {$startupList}"     : "A task assigned to {$startupList} has been archived: {$taskTitle}",
               // 'reopen'  => $isAr ? "تمت إعادة فتح المهمة: {$taskTitle} - الشركات: {$startupList}" : "Task reopened: {$taskTitle} - Startups: {$startupList}",
               // default   => $isAr ? "تحديث على المهمة: {$taskTitle}" : "Task update: {$taskTitle}",
        //    };
       // }

        return [
            'task_id' => $this->data['task']->id,
            'title'   => $taskTitle,
            'message' => $message,
        ];
    }

    private function getSubject(): string
    {
        $action = $this->data['action'] ?? 'add';
        $title  = $this->data['task']->title ?? '';

        if ($action === 'feedback' && isset($this->data['decision'])) {
            $isAr = app()->getLocale() === 'ar';
            if ($this->data['decision'] === 'pass') {
                return $isAr
                    ? "تحديث مراجعة المهمة: تم اجتياز {$title}."
                    : "Task review update: {$title} passed.";
            }
            return $isAr
                ? "تحديث مراجعة المهمة: مطلوب تعديلات على {$title}."
                : "Task review update: Revisions requested for {$title}.";
        }

        $key    = "notifications.task.subject_{$action}";
        $translated = __($key);
        $base   = $translated !== $key ? $translated : __('notifications.task.subject');
        return $title ? "{$base}: {$title}" : $base;
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

    /**
     * The organization the task belongs to (through its mentorship session or
     * cycle), not the recipient's own organization_id.
     */
    protected function taskOrganization($notifiable)
    {
        $task = $this->data['task'] ?? null;
        $session = $task instanceof MentorshipTask ? $task->mentorshipRequest : null;
        $cycle = match (true) {
            $task instanceof MentorshipTask => $task->company?->cycledata,
            $task instanceof TodoList => $task->cycle,
            $task instanceof ProgramTask => $task->program,
            default => null,
        };

        return $this->sessionOrganization($notifiable, $session, $cycle?->organization_id);
    }
}
