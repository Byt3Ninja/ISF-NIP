<?php

use Illuminate\Database\Seeder;
use App\NotificationTemplate;
use App\User;

class NotificationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the first admin user or create a system user
        $adminUser = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first();
        
        if (!$adminUser) {
            $adminUser = User::first();
        }
        
        if (!$adminUser) {
            $this->command->error('No users found. Please create at least one user before running this seeder.');
            return;
        }

        $templates = [
            [
                'name' => 'welcome_email',
                'title' => 'Welcome to VI Portal',
                'description' => 'Welcome email template for new users',
                'channel' => 'email',
                'subject' => 'Welcome to {{system.app_name}}, {{user.first_name}}!',
                'content' => 'Dear {{user.name}},

Welcome to {{system.app_name}}! We\'re excited to have you join our community.

Your account has been successfully created with the email address: {{user.email}}

Here are some quick tips to get you started:
- Complete your profile to help others connect with you
- Explore the available programs and opportunities
- Connect with mentors and other entrepreneurs

If you have any questions, please don\'t hesitate to reach out to our support team at {{system.support_email}}.

Best regards,
The {{system.app_name}} Team',
                'sender' => 'noreply@viportal.com',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'mentorship_request_notification',
                'title' => 'New Mentorship Request',
                'description' => 'Notification for mentors when they receive a new mentorship request',
                'channel' => 'email',
                'subject' => 'New Mentorship Request from {{user.name}}',
                'content' => 'Hello {{mentor.name}},

You have received a new mentorship request from {{user.name}} ({{user.email}}).

Request Details:
- Requested by: {{user.name}}
- Date: {{system.date}}
- Message: {{request.message}}

Please log in to your dashboard to review and respond to this request.

{{action_url}}

Best regards,
The {{system.app_name}} Team',
                'sender' => 'mentorship@viportal.com',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'training_reminder',
                'title' => 'Training Session Reminder',
                'description' => 'Reminder notification for upcoming training sessions',
                'channel' => 'email',
                'subject' => 'Reminder: {{training.title}} - Tomorrow at {{training.time}}',
                'content' => 'Hi {{user.first_name}},

This is a friendly reminder about your upcoming training session:

Training: {{training.title}}
Date: {{training.date}}
Time: {{training.time}}
Location: {{training.location}}

Please make sure to:
- Join the session on time
- Bring any required materials
- Have a stable internet connection if it\'s an online session

If you need to cancel or reschedule, please contact us at {{system.support_email}} as soon as possible.

See you there!
The {{system.app_name}} Team',
                'sender' => 'training@viportal.com',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'whatsapp_welcome',
                'title' => 'Welcome Message',
                'description' => 'WhatsApp welcome message for new users',
                'channel' => 'whatsapp',
                'content' => 'Welcome to {{system.app_name}}, {{user.first_name}}! 🎉

Your account is now active. Start exploring opportunities and connect with our community.

Need help? Reply with "HELP" or contact {{system.support_email}}',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'push_notification_reminder',
                'title' => 'Task Reminder',
                'description' => 'Push notification for task reminders',
                'channel' => 'pusher',
                'content' => '{{user.first_name}}, you have a pending task: {{task.title}}

Due: {{task.due_date}}
Priority: {{task.priority}}

Tap to view details.',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'password_reset_email',
                'title' => 'Password Reset Request',
                'description' => 'Email template for password reset requests',
                'channel' => 'email',
                'subject' => 'Reset Your {{system.app_name}} Password',
                'content' => 'Hello {{user.name}},

We received a request to reset your password for your {{system.app_name}} account.

If you requested this password reset, please click the link below to set a new password:

{{reset_url}}

This link will expire in 60 minutes for security reasons.

If you did not request a password reset, please ignore this email. Your password will remain unchanged.

For security questions, contact us at {{system.support_email}}.

Best regards,
The {{system.app_name}} Security Team',
                'sender' => 'security@viportal.com',
                'created_by' => $adminUser->id,
            ],
            [
                'name' => 'application_status_update',
                'title' => 'Application Status Update',
                'description' => 'Notification for application status changes',
                'channel' => 'email',
                'subject' => 'Your Application Status Has Been Updated',
                'content' => 'Dear {{user.name}},

We wanted to update you on the status of your application for {{program.name}}.

Application Status: {{application.status}}
Updated on: {{system.datetime}}

{{#if application.approved}}
Congratulations! Your application has been approved. You will receive further instructions shortly.
{{else}}
{{#if application.rejected}}
Unfortunately, your application was not successful this time. We encourage you to apply for future opportunities.
{{else}}
Your application is still under review. We will notify you of any updates.
{{/if}}
{{/if}}

If you have any questions about your application, please contact us at {{system.support_email}}.

Best regards,
The {{system.app_name}} Admissions Team',
                'sender' => 'admissions@viportal.com',
                'created_by' => $adminUser->id,
            ]
        ];

        foreach ($templates as $templateData) {
            $template = NotificationTemplate::create($templateData);
            $template->variables = $template->extractVariables();
            $template->save();
            
            $this->command->info("Created template: {$template->name}");
        }

        $this->command->info('Notification templates seeded successfully!');
    }
}
