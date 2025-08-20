<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NotificationTrigger extends Model
{
    protected $fillable = [
        'name',
        'title',
        'description',
        'event',
        'conditions',
        'template_id',
        'recipient_rules',
        'is_active',
        'priority',
        'settings',
        'created_by'
    ];

    protected $casts = [
        'conditions' => 'array',
        'recipient_rules' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the notification template associated with this trigger.
     */
    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    /**
     * Get the user who created this trigger.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all active triggers for a specific event.
     */
    public static function getActiveTriggersForEvent($event)
    {
        return static::where('event', $event)
            ->where('is_active', true)
            ->with('template')
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Check if this trigger's conditions are met for the given data.
     */
    public function conditionsAreMet($data)
    {
        if (empty($this->conditions)) {
            return true;
        }

        foreach ($this->conditions as $field => $expectedValue) {
            $actualValue = data_get($data, $field);
            
            // Handle different comparison types
            if (is_array($expectedValue)) {
                // Array means "in" condition
                if (!in_array($actualValue, $expectedValue)) {
                    return false;
                }
            } else {
                // Direct comparison
                if ($actualValue != $expectedValue) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Determine who should receive notifications based on recipient rules.
     */
    public function getRecipients($data)
    {
        $recipients = [];

        foreach ($this->recipient_rules as $rule) {
            switch ($rule['type']) {
                case 'self':
                    // Send to the user who triggered the event
                    if (isset($data['user'])) {
                        $recipients[] = $data['user'];
                    }
                    break;

                case 'role':
                    // Send to users with specific roles
                    $users = User::role($rule['value'])->get();
                    $recipients = array_merge($recipients, $users->toArray());
                    break;

                case 'email':
                    // Send to specific email addresses
                    $recipients[] = ['email' => $rule['value']];
                    break;

                case 'admin':
                    // Send to all admin users
                    $admins = User::role('Admin')->get();
                    $recipients = array_merge($recipients, $admins->toArray());
                    break;

                case 'organization_managers':
                    // Send to organization managers
                    $managers = User::role('organizationManager')->get();
                    $recipients = array_merge($recipients, $managers->toArray());
                    break;

                case 'custom':
                    // Custom logic based on data
                    if (isset($rule['field']) && isset($data[$rule['field']])) {
                        $user = User::find($data[$rule['field']]);
                        if ($user) {
                            $recipients[] = $user;
                        }
                    }
                    break;
            }
        }

        // Remove duplicates based on email or ID
        return collect($recipients)->unique(function ($recipient) {
            return is_array($recipient) ? $recipient['email'] ?? $recipient['id'] : $recipient->email ?? $recipient->id;
        })->values()->all();
    }

    /**
     * Get available events for triggers.
     */
    public static function getAvailableEvents()
    {
        return [
            'user.created' => 'User Created',
            'user.updated' => 'User Updated',
            'user.role_assigned' => 'User Role Assigned',
            'user.role_removed' => 'User Role Removed',
            'user.password_reset' => 'Password Reset Requested',
            'user.email_verified' => 'Email Verified',
            'user.login' => 'User Login',
            'order.created' => 'Order Created',
            'order.updated' => 'Order Updated',
            'order.completed' => 'Order Completed',
            'order.cancelled' => 'Order Cancelled',
            'company.created' => 'Company Created',
            'company.updated' => 'Company Updated',
            'system.maintenance' => 'System Maintenance',
            'system.backup_completed' => 'Backup Completed',
        ];
    }

    /**
     * Get available recipient rule types.
     */
    public static function getRecipientRuleTypes()
    {
        return [
            'self' => 'The user who triggered the event',
            'role' => 'Users with specific role',
            'email' => 'Specific email address',
            'admin' => 'All admin users',
            'organization_managers' => 'All organization managers',
            'custom' => 'Custom field from event data'
        ];
    }

    /**
     * Get sample conditions for different events.
     */
    public static function getSampleConditions($event)
    {
        $samples = [
            'user.created' => [
                'role' => ['Admin', 'organizationManager'],
                'status' => 'active'
            ],
            'user.role_assigned' => [
                'role' => 'Admin'
            ],
            'order.created' => [
                'status' => 'pending',
                'total' => ['>', 100]
            ]
        ];

        return $samples[$event] ?? [];
    }

    /**
     * Clone this trigger with new data.
     */
    public function cloneTrigger($updates = [])
    {
        $cloned = $this->replicate();
        $cloned->name = $updates['name'] ?? $this->name . ' (Copy)';
        $cloned->created_by = $updates['created_by'] ?? auth()->id();
        $cloned->is_active = false; // Start as inactive
        
        foreach ($updates as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $cloned->{$key} = $value;
            }
        }
        
        $cloned->save();
        return $cloned;
    }
}
