<?php

namespace App\Services;

use App\NotificationTemplate;
use App\Notifications\TemplateNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationTemplateService
{
    /**
     * Send a notification using a template object.
     *
     * @param NotificationTemplate $template
     * @param mixed $notifiable
     * @param array $data
     * @param array $channels Optional channels override
     * @return bool
     */
    public function sendTemplateNotification(NotificationTemplate $template, $notifiable, array $data = [], array $channels = []): bool
    {
        try {
            $notification = new TemplateNotification($template->name, $data, $channels);
            
            if (is_array($notifiable)) {
                Notification::send($notifiable, $notification);
            } else {
                $notifiable->notify($notification);
            }
            
            Log::info('Template notification sent successfully', [
                'template' => $template->name,
                'template_id' => $template->id,
                'notifiable_type' => is_array($notifiable) ? 'multiple' : get_class($notifiable),
                'notifiable_count' => is_array($notifiable) ? count($notifiable) : 1
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send template notification', [
                'template' => $template->name,
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Send a notification using a template by name.
     *
     * @param mixed $notifiable
     * @param string $templateName
     * @param array $data
     * @param array $channels Optional channels override
     * @return bool
     */
    public function send($notifiable, string $templateName, array $data = [], array $channels = []): bool
    {
        try {
            $notification = new TemplateNotification($templateName, $data, $channels);
            
            if (is_array($notifiable)) {
                Notification::send($notifiable, $notification);
            } else {
                $notifiable->notify($notification);
            }
            
            Log::info('Template notification sent successfully', [
                'template' => $templateName,
                'notifiable_type' => is_array($notifiable) ? 'multiple' : get_class($notifiable),
                'notifiable_count' => is_array($notifiable) ? count($notifiable) : 1
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send template notification', [
                'template' => $templateName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Get a template by name.
     *
     * @param string $name
     * @return NotificationTemplate|null
     */
    public function getTemplate(string $name): ?NotificationTemplate
    {
        return NotificationTemplate::where('name', $name)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create a new template.
     *
     * @param array $data
     * @return NotificationTemplate
     */
    public function createTemplate(array $data): NotificationTemplate
    {
        $template = new NotificationTemplate($data);
        $template->variables = $template->extractVariables();
        $template->save();
        
        Log::info('Notification template created', [
            'template_id' => $template->id,
            'name' => $template->name,
            'channel' => $template->channel
        ]);
        
        return $template;
    }

    /**
     * Update an existing template.
     *
     * @param NotificationTemplate $template
     * @param array $data
     * @return NotificationTemplate
     */
    public function updateTemplate(NotificationTemplate $template, array $data): NotificationTemplate
    {
        $template->fill($data);
        $template->variables = $template->extractVariables();
        $template->save();
        
        Log::info('Notification template updated', [
            'template_id' => $template->id,
            'name' => $template->name,
            'channel' => $template->channel
        ]);
        
        return $template;
    }

    /**
     * Clone a template.
     *
     * @param NotificationTemplate $template
     * @param array $updates
     * @return NotificationTemplate
     */
    public function cloneTemplate(NotificationTemplate $template, array $updates = []): NotificationTemplate
    {
        $cloned = $template->cloneTemplate($updates);
        
        Log::info('Notification template cloned', [
            'original_id' => $template->id,
            'cloned_id' => $cloned->id,
            'name' => $cloned->name
        ]);
        
        return $cloned;
    }

    /**
     * Preview a template with sample data.
     *
     * @param NotificationTemplate $template
     * @param array $customData
     * @return array
     */
    public function previewTemplate(NotificationTemplate $template, array $customData = []): array
    {
        $sampleData = array_merge(NotificationTemplate::getSampleData(), $customData);
        $renderedContent = $template->render($sampleData);
        
        return [
            'rendered_content' => $renderedContent,
            'sample_data' => $sampleData,
            'variables' => $template->variables,
            'template' => $template
        ];
    }

    /**
     * Get templates by channel.
     *
     * @param string $channel
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTemplatesByChannel(string $channel, bool $activeOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = NotificationTemplate::byChannel($channel);
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->get();
    }

    /**
     * Get all available channels.
     *
     * @return array
     */
    public function getAvailableChannels(): array
    {
        return ['email', 'whatsapp', 'pusher'];
    }

    /**
     * Get template statistics.
     *
     * @return array
     */
    public function getTemplateStats(): array
    {
        $total = NotificationTemplate::count();
        $active = NotificationTemplate::active()->count();
        $byChannel = NotificationTemplate::selectRaw('channel, count(*) as count')
            ->groupBy('channel')
            ->pluck('count', 'channel')
            ->toArray();
            
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'by_channel' => $byChannel
        ];
    }

    /**
     * Validate template content for required variables.
     *
     * @param string $content
     * @param array $requiredVars
     * @return array
     */
    public function validateTemplate(string $content, array $requiredVars = []): array
    {
        $errors = [];
        $extractedVars = [];
        
        // Extract variables from content
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        $extractedVars = array_unique($matches[1]);
        
        // Check for required variables
        foreach ($requiredVars as $requiredVar) {
            if (!in_array($requiredVar, $extractedVars)) {
                $errors[] = "Required variable '{{$requiredVar}}' is missing from template content";
            }
        }
        
        // Check for malformed variables
        preg_match_all('/\{[^{]|[^}]\}/', $content, $malformedMatches);
        if (!empty($malformedMatches[0])) {
            $errors[] = "Template contains malformed variable placeholders";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'variables' => $extractedVars
        ];
    }

    /**
     * Test send a template notification.
     *
     * @param NotificationTemplate $template
     * @param string $recipient
     * @param array $testData
     * @return bool
     */
    public function testSend(NotificationTemplate $template, string $recipient, array $testData = []): bool
    {
        try {
            $data = array_merge(NotificationTemplate::getSampleData(), $testData);
            
            // Create a mock user for testing
            $testUser = new \stdClass();
            $testUser->email = $recipient;
            $testUser->first_name_en = 'Test';
            $testUser->last_name_en = 'User';
            
            // For email templates, we can actually send a test email
            if ($template->channel === 'email' && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $notification = new TemplateNotification($template->name, $data, ['mail']);
                \Illuminate\Support\Facades\Notification::route('mail', $recipient)
                    ->notify($notification);
                
                Log::info('Test email sent', [
                    'template' => $template->name,
                    'recipient' => $recipient
                ]);
                
                return true;
            }
            
            // For other channels, just log the test
            Log::info('Test notification simulated', [
                'template' => $template->name,
                'channel' => $template->channel,
                'recipient' => $recipient,
                'rendered_content' => $template->render($data)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Test notification failed', [
                'template' => $template->name,
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
