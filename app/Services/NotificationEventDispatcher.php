<?php

namespace App\Services;

use App\NotificationTrigger;
use App\Services\NotificationTemplateService;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationEventDispatcher
{
    protected $templateService;

    public function __construct(NotificationTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Dispatch notifications for a specific event.
     */
    public function dispatch($event, $data = [])
    {
        try {
            Log::info("NotificationEventDispatcher: Dispatching event '{$event}'", ['data' => $data]);

            // Get all active triggers for this event
            $triggers = NotificationTrigger::getActiveTriggersForEvent($event);

            if ($triggers->isEmpty()) {
                Log::info("NotificationEventDispatcher: No active triggers found for event '{$event}'");
                return;
            }

            Log::info("NotificationEventDispatcher: Found {$triggers->count()} triggers for event '{$event}'");

            foreach ($triggers as $trigger) {
                $this->processTrigger($trigger, $data);
            }

        } catch (Exception $e) {
            Log::error("NotificationEventDispatcher: Error dispatching event '{$event}': " . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
        }
    }

    /**
     * Process a single trigger.
     */
    protected function processTrigger(NotificationTrigger $trigger, $data)
    {
        try {
            Log::info("NotificationEventDispatcher: Processing trigger '{$trigger->name}'");

            // Check if trigger conditions are met
            if (!$trigger->conditionsAreMet($data)) {
                Log::info("NotificationEventDispatcher: Trigger '{$trigger->name}' conditions not met");
                return;
            }

            // Get recipients for this trigger
            $recipients = $trigger->getRecipients($data);

            if (empty($recipients)) {
                Log::warning("NotificationEventDispatcher: No recipients found for trigger '{$trigger->name}'");
                return;
            }

            Log::info("NotificationEventDispatcher: Found " . count($recipients) . " recipients for trigger '{$trigger->name}'");

            // Send notifications to each recipient
            foreach ($recipients as $recipient) {
                $this->sendNotificationToRecipient($trigger, $recipient, $data);
            }

        } catch (Exception $e) {
            Log::error("NotificationEventDispatcher: Error processing trigger '{$trigger->name}': " . $e->getMessage(), [
                'exception' => $e,
                'trigger_id' => $trigger->id
            ]);
        }
    }

    /**
     * Send notification to a specific recipient.
     */
    protected function sendNotificationToRecipient(NotificationTrigger $trigger, $recipient, $data)
    {
        try {
            // Prepare recipient data
            $recipientData = $this->prepareRecipientData($recipient);
            
            if (!$recipientData) {
                Log::warning("NotificationEventDispatcher: Could not prepare recipient data", ['recipient' => $recipient]);
                return;
            }

            // Merge event data with recipient data for template rendering
            $templateData = array_merge($data, ['recipient' => $recipientData]);

            Log::info("NotificationEventDispatcher: Sending notification to {$recipientData['email']} using template '{$trigger->template->name}'");

            // Send the notification using the template service
            $this->templateService->sendTemplateNotification(
                $trigger->template,
                $recipientData,
                $templateData
            );

            Log::info("NotificationEventDispatcher: Successfully sent notification to {$recipientData['email']}");

        } catch (Exception $e) {
            Log::error("NotificationEventDispatcher: Error sending notification to recipient: " . $e->getMessage(), [
                'exception' => $e,
                'trigger_id' => $trigger->id,
                'recipient' => $recipient
            ]);
        }
    }

    /**
     * Prepare recipient data for notification sending.
     */
    protected function prepareRecipientData($recipient)
    {
        if (is_array($recipient)) {
            // Already prepared data or email-only recipient
            return $recipient;
        }

        if (is_object($recipient) && method_exists($recipient, 'toArray')) {
            // Eloquent model
            return $recipient->toArray();
        }

        if (is_object($recipient)) {
            // Generic object
            return (array) $recipient;
        }

        return null;
    }

    /**
     * Convenience methods for common events.
     */
    public function userCreated($user, $additionalData = [])
    {
        $data = array_merge([
            'user' => $user->toArray(),
            'event_type' => 'user_created'
        ], $additionalData);

        $this->dispatch('user.created', $data);
    }

    public function userRoleAssigned($user, $role, $additionalData = [])
    {
        $data = array_merge([
            'user' => $user->toArray(),
            'role' => $role,
            'event_type' => 'user_role_assigned'
        ], $additionalData);

        $this->dispatch('user.role_assigned', $data);
    }

    public function orderCreated($order, $user = null, $additionalData = [])
    {
        $data = array_merge([
            'order' => is_array($order) ? $order : $order->toArray(),
            'event_type' => 'order_created'
        ], $additionalData);

        if ($user) {
            $data['user'] = is_array($user) ? $user : $user->toArray();
        }

        $this->dispatch('order.created', $data);
    }

    public function passwordResetRequested($user, $token, $additionalData = [])
    {
        $data = array_merge([
            'user' => $user->toArray(),
            'reset_token' => $token,
            'event_type' => 'password_reset'
        ], $additionalData);

        $this->dispatch('user.password_reset', $data);
    }

    public function emailVerified($user, $additionalData = [])
    {
        $data = array_merge([
            'user' => $user->toArray(),
            'event_type' => 'email_verified'
        ], $additionalData);

        $this->dispatch('user.email_verified', $data);
    }

    /**
     * Test a trigger with sample data.
     */
    public function testTrigger(NotificationTrigger $trigger, $testData = [])
    {
        Log::info("NotificationEventDispatcher: Testing trigger '{$trigger->name}'");

        // Use sample data if none provided
        if (empty($testData)) {
            $testData = $this->getSampleDataForEvent($trigger->event);
        }

        $this->processTrigger($trigger, $testData);
    }

    /**
     * Get sample data for testing different events.
     */
    protected function getSampleDataForEvent($event)
    {
        $sampleData = [
            'user.created' => [
                'user' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'first_name' => 'John',
                    'last_name' => 'Doe'
                ],
                'role' => 'Admin'
            ],
            'user.role_assigned' => [
                'user' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com'
                ],
                'role' => 'Admin',
                'assigned_by' => 'System Administrator'
            ],
            'order.created' => [
                'order' => [
                    'id' => 12345,
                    'total' => 299.99,
                    'date' => now()->format('Y-m-d'),
                    'status' => 'pending'
                ],
                'user' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com'
                ]
            ]
        ];

        return $sampleData[$event] ?? [];
    }
}
