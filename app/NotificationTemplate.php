<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'title',
        'description',
        'channel',
        'subject',
        'content',
        'variables',
        'settings',
        'sender',
        'is_active',
        'version',
        'parent_template_id',
        'created_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user who created this template
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent template (for versioning)
     */
    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'parent_template_id');
    }

    /**
     * Get child templates (versions/clones)
     */
    public function childTemplates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class, 'parent_template_id');
    }

    /**
     * Scope to get active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by channel
     */
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Render the template content with provided variables
     */
    public function render(array $data = []): string
    {
        $content = $this->content;
        
        // Replace placeholders with actual values
        foreach ($data as $key => $value) {
            // Support nested object notation like {{user.name}}
            if (is_object($value) || is_array($value)) {
                $this->replaceNestedPlaceholders($content, $key, $value);
            } else {
                $content = str_replace('{{' . $key . '}}', $value, $content);
            }
        }
        
        return $content;
    }

    /**
     * Replace nested placeholders like {{user.name}}
     */
    private function replaceNestedPlaceholders(string &$content, string $prefix, $data): void
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
     * Extract variables from template content
     */
    public function extractVariables(): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $this->content, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Clone template with new version
     */
    public function cloneTemplate(array $updates = []): NotificationTemplate
    {
        $newTemplate = $this->replicate();
        $newTemplate->parent_template_id = $this->id;
        $newTemplate->version = $this->getNextVersion();
        
        foreach ($updates as $key => $value) {
            $newTemplate->$key = $value;
        }
        
        $newTemplate->save();
        return $newTemplate;
    }

    /**
     * Get next version number for this template family
     */
    private function getNextVersion(): int
    {
        $parentId = $this->parent_template_id ?? $this->id;
        
        return NotificationTemplate::where('parent_template_id', $parentId)
            ->orWhere('id', $parentId)
            ->max('version') + 1;
    }

    /**
     * Get sample data for preview
     */
    public static function getSampleData(): array
    {
        return [
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe'
            ],
            'company' => [
                'name' => 'Sample Company',
                'website' => 'https://example.com'
            ],
            'order' => [
                'id' => '12345',
                'total' => '$99.99',
                'date' => date('Y-m-d')
            ],
            'system' => [
                'app_name' => 'VI Portal',
                'support_email' => 'support@viportal.com'
            ]
        ];
    }
}
