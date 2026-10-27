<?php

namespace App\Notifications;

use App\NotificationTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Throwable;
use App\Services\TimezoneService;

class TemplateNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

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
     * Template name.
     *
     * @var string
     */
    protected $templateName;

    /**
     * Template data.
     *
     * @var array
     */
    protected $templateData;

    /**
     * Channels override.
     *
     * @var array
     */
    protected $channels;

    /**
     * Template instance.
     *
     * @var NotificationTemplate
     */
    protected $template;

    /**
     * Create a new notification instance.
     *
     * @param string $templateName
     * @param array $templateData
     * @param array $channels Optional array of channels to override template default
     */
    public function __construct(string $templateName, array $templateData = [], array $channels = [])
    {
        $this->templateName = $templateName;
        $this->templateData = $templateData;
        $this->channels = $channels;
        
        // Load the template
        $this->template = NotificationTemplate::where('name', $templateName)
            ->where('is_active', true)
            ->first();
            
        if (!$this->template) {
            Log::error("Notification template '{$templateName}' not found or inactive");
            throw new \Exception("Notification template '{$templateName}' not found or inactive");
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Use provided channels or determine from template
        if (!empty($this->channels)) {
            return $this->channels;
        }

        $channels = [];
        
        switch ($this->template->channel) {
            case 'email':
                $channels = ['mail', 'database'];
                break;
            case 'whatsapp':
                $channels = ['database']; // Add WhatsApp channel when implemented
                break;
            case 'pusher':
                $channels = ['broadcast', 'database'];
                break;
            default:
                $channels = ['database'];
        }

        Log::info('TemplateNotification channels determined', [
            'template' => $this->templateName,
            'channels' => $channels,
            'user' => $notifiable->id ?? 'unknown'
        ]);

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->template->channel !== 'email') {
            Log::warning('Attempting to send email for non-email template', [
                'template' => $this->templateName,
                'channel' => $this->template->channel
            ]);
        }

        // Prepare template data with notifiable user data
        $data = $this->prepareTemplateData($notifiable);
        
        // Render the template content
        $renderedContent = $this->template->render($data);
        $renderedSubject = $this->template->subject ? $this->renderString($this->template->subject, $data) : $this->template->title;

        $mailMessage = (new MailMessage)
            ->subject($renderedSubject)
            ->greeting('') // We'll handle greeting in the template
            ->line($renderedContent);

        // Add action if URL is provided in template data
        if (isset($data['action_url']) && isset($data['action_text'])) {
            $mailMessage->action($data['action_text'], $data['action_url']);
        }

        // Set sender if specified
        if (!empty($this->template->sender) && filter_var($this->template->sender, FILTER_VALIDATE_EMAIL)) {
            $mailMessage->from($this->template->sender);
        } else {
            $mailMessage->from(config('mail.from.address'), config('mail.from.name'));
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        $data = $this->prepareTemplateData($notifiable);
        $renderedContent = $this->template->render($data);

        Log::info('TemplateNotification stored to database', [
            'template' => $this->templateName,
            'notifiable' => $notifiable->id ?? 'unknown',
            'channel' => $this->template->channel
        ]);

        return [
            'template_id' => $this->template->id,
            'template_name' => $this->templateName,
            'channel' => $this->template->channel,
            'title' => $this->template->title,
            'message' => $renderedContent,
            'data' => $data
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toBroadcast($notifiable): array
    {
        $data = $this->prepareTemplateData($notifiable);
        $renderedContent = $this->template->render($data);

        return [
            'template_id' => $this->template->id,
            'template_name' => $this->templateName,
            'title' => $this->template->title,
            'message' => $renderedContent,
            'channel' => $this->template->channel,
            'data' => $data
        ];
    }

    /**
     * Prepare template data by merging provided data with user data.
     *
     * @param mixed $notifiable
     * @return array
     */
    protected function prepareTemplateData($notifiable): array
    {
        $userData = [];
        
        if ($notifiable) {
            $userData = [
                'user' => [
                    'id' => $notifiable->id ?? null,
                    'name' => $this->getUserName($notifiable),
                    'email' => $notifiable->email ?? null,
                    'first_name' => $notifiable->first_name_en ?? $notifiable->first_name ?? null,
                    'last_name' => $notifiable->last_name_en ?? $notifiable->last_name ?? null,
                ]
            ];
        }

        // GV-9566: "now" as the recipient reads it (their zone, else the platform's).
        $recipientNow = now()->setTimezone(TimezoneService::viewer($notifiable) ?? TimezoneService::platform());

        // Add system data
        $systemData = [
            'system' => [
                'app_name' => config('app.name', 'VI Portal'),
                'app_url' => config('app.url'),
                'support_email' => config('mail.support_address', 'support@viportal.com'),
                'date' => $recipientNow->format('Y-m-d'),
                'time' => $recipientNow->format('H:i:s'),
                'datetime' => $recipientNow->format('Y-m-d H:i:s')
            ]
        ];

        return array_merge($userData, $systemData, $this->templateData);
    }

    /**
     * Get user's display name.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function getUserName($notifiable): string
    {
        if (!$notifiable) {
            return 'User';
        }

        // Try different name combinations
        if (isset($notifiable->first_name_en) && isset($notifiable->last_name_en)) {
            return trim($notifiable->first_name_en . ' ' . $notifiable->last_name_en);
        }
        
        if (isset($notifiable->first_name) && isset($notifiable->last_name)) {
            return trim($notifiable->first_name . ' ' . $notifiable->last_name);
        }
        
        if (isset($notifiable->name)) {
            return $notifiable->name;
        }
        
        if (isset($notifiable->email)) {
            return explode('@', $notifiable->email)[0];
        }

        return 'User';
    }

    /**
     * Render a string with template data.
     *
     * @param string $string
     * @param array $data
     * @return string
     */
    protected function renderString(string $string, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $this->replaceNestedPlaceholders($string, $key, $value);
            } else {
                $string = str_replace('{{' . $key . '}}', $value, $string);
            }
        }
        
        return $string;
    }

    /**
     * Replace nested placeholders like {{user.name}}.
     *
     * @param string $content
     * @param string $prefix
     * @param mixed $data
     */
    protected function replaceNestedPlaceholders(string &$content, string $prefix, $data): void
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                $placeholder = $prefix . '.' . $key;
                if (is_scalar($value)) {
                    $content = str_replace('{{' . $placeholder . '}}', $value, $content);
                } elseif (is_array($value) || is_object($value)) {
                    $this->replaceNestedPlaceholders($content, $placeholder, $value);
                }
            }
        }
    }

    /**
     * Get the template instance.
     *
     * @return NotificationTemplate
     */
    public function getTemplate(): NotificationTemplate
    {
        return $this->template;
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('TemplateNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'template_name' => $this->templateName,
            'template_data' => $this->templateData,
        ]);
    }
}
