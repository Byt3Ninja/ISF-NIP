<?php

namespace App\Http\Controllers;

use App\NotificationTemplate;
use App\Services\NotificationTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationTemplateDemoController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationTemplateService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Show the demo page with working examples
     */
    public function index()
    {
        $templates = NotificationTemplate::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $demoData = $this->getDemoData();
        
        return view('admin.notification-templates.demo', compact('templates', 'demoData'));
    }

    /**
     * Create sample templates for demonstration
     */
    public function createSamples()
    {
        try {
            $this->createWelcomeEmailTemplate();
            $this->createPasswordResetTemplate();
            $this->createWhatsAppWelcomeTemplate();
            $this->createTaskReminderTemplate();

            return response()->json([
                'success' => true,
                'message' => 'Sample templates created successfully!',
                'templates_created' => 4
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create sample templates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sample templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a test notification using a template
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:notification_templates,id',
            'recipient' => 'required|email',
            'test_data' => 'array'
        ]);

        try {
            $template = NotificationTemplate::findOrFail($request->template_id);
            $testData = $request->test_data ?? $this->getDemoData();

            // Send the test notification
            $result = $this->notificationService->send(
                $template->name,
                $testData,
                $request->recipient
            );

            return response()->json([
                'success' => true,
                'message' => 'Test notification sent successfully!',
                'template' => $template->name,
                'recipient' => $request->recipient
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send test notification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview a template with sample data
     */
    public function preview(Request $request, NotificationTemplate $template)
    {
        try {
            $demoData = $request->input('data', $this->getDemoData());
            
            $preview = $this->notificationService->preview($template->name, $demoData);
            
            return response()->json([
                'success' => true,
                'preview' => $preview,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to preview template: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get demo data for testing templates
     */
    private function getDemoData()
    {
        return [
            'user' => [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'plan' => 'Premium',
                'created_at' => now()->format('M j, Y'),
                'avatar' => 'https://ui-avatars.com/api/?name=John+Doe&background=007bff&color=fff'
            ],
            'app' => [
                'name' => config('app.name', 'Growth Labs'),
                'url' => config('app.url', 'https://growthlabs.com'),
                'logo' => asset('images/logo.png'),
                'support_email' => 'support@growthlabs.com',
                'phone' => '+1-800-GROWTH'
            ],
            'company' => [
                'name' => 'Growth Labs Inc.',
                'address' => '123 Innovation Street, Tech City, TC 12345',
                'website' => 'https://growthlabs.com'
            ],
            'system' => [
                'date' => now()->format('F j, Y'),
                'time' => now()->format('g:i A'),
                'timestamp' => now()->timestamp,
                'year' => now()->year,
                'month' => now()->format('F')
            ],
            'custom' => [
                'promo_code' => 'WELCOME2024',
                'discount_amount' => '20%',
                'event_name' => 'Annual Growth Summit',
                'deadline' => now()->addDays(7)->format('M j, Y')
            ]
        ];
    }

    /**
     * Create welcome email template
     */
    private function createWelcomeEmailTemplate()
    {
        return NotificationTemplate::updateOrCreate(
            ['name' => 'welcome-email-demo'],
            [
                'title' => 'Welcome Email (Demo)',
                'description' => 'A comprehensive welcome email template with best practices',
                'channel' => 'email',
                'subject' => 'Welcome to {{app.name}}, {{user.name}}! 🎉',
                'sender' => 'welcome@growthlabs.com',
                'content' => $this->getWelcomeEmailContent(),
                'is_active' => true,
                'created_by' => Auth::id() ?? 1,
                'variables' => ['user.name', 'user.email', 'user.plan', 'user.created_at', 'app.name', 'app.url', 'custom.promo_code']
            ]
        );
    }

    /**
     * Create password reset template
     */
    private function createPasswordResetTemplate()
    {
        return NotificationTemplate::updateOrCreate(
            ['name' => 'password-reset-demo'],
            [
                'title' => 'Password Reset (Demo)',
                'description' => 'Secure password reset notification template',
                'channel' => 'email',
                'subject' => 'Reset Your {{app.name}} Password',
                'sender' => 'security@growthlabs.com',
                'content' => $this->getPasswordResetContent(),
                'is_active' => true,
                'created_by' => Auth::id() ?? 1,
                'variables' => ['user.name', 'app.name', 'app.url', 'system.date', 'custom.deadline']
            ]
        );
    }

    /**
     * Create WhatsApp welcome template
     */
    private function createWhatsAppWelcomeTemplate()
    {
        return NotificationTemplate::updateOrCreate(
            ['name' => 'whatsapp-welcome-demo'],
            [
                'title' => 'WhatsApp Welcome (Demo)',
                'description' => 'Mobile-friendly welcome message for WhatsApp',
                'channel' => 'whatsapp',
                'sender' => 'Growth Labs',
                'content' => $this->getWhatsAppWelcomeContent(),
                'is_active' => true,
                'created_by' => Auth::id() ?? 1,
                'variables' => ['user.name', 'user.plan', 'app.name', 'app.url', 'custom.promo_code']
            ]
        );
    }

    /**
     * Create task reminder template
     */
    private function createTaskReminderTemplate()
    {
        return NotificationTemplate::updateOrCreate(
            ['name' => 'task-reminder-demo'],
            [
                'title' => 'Task Reminder (Demo)',
                'description' => 'Friendly reminder for pending tasks',
                'channel' => 'email',
                'subject' => 'Reminder: Complete Your Profile Setup',
                'sender' => 'reminders@growthlabs.com',
                'content' => $this->getTaskReminderContent(),
                'is_active' => true,
                'created_by' => Auth::id() ?? 1,
                'variables' => ['user.name', 'app.name', 'app.url', 'custom.deadline']
            ]
        );
    }

    /**
     * Get welcome email content
     */
    private function getWelcomeEmailContent()
    {
        return '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <div style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 40px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 28px; font-weight: 600;">Welcome to {{app.name}}!</h1>
        <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Your journey to growth starts here</p>
    </div>
    
    <div style="padding: 40px 20px;">
        <h2 style="color: #333; font-size: 22px; margin-bottom: 20px;">Hi {{user.name}},</h2>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">We\'re thrilled to have you join our community! Your account has been successfully created and you\'re now ready to start your journey with us.</p>
        
        <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin: 30px 0; border-left: 4px solid #007bff;">
            <h3 style="color: #007bff; margin-top: 0;">Your Account Details:</h3>
            <ul style="color: #666; line-height: 1.8;">
                <li><strong>Email:</strong> {{user.email}}</li>
                <li><strong>Account Type:</strong> {{user.plan}}</li>
                <li><strong>Member Since:</strong> {{user.created_at}}</li>
            </ul>
        </div>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">Here are some quick steps to get you started:</p>
        <ol style="color: #666; line-height: 1.8; font-size: 16px;">
            <li>Complete your profile setup</li>
            <li>Explore our dashboard features</li>
            <li>Join our community forum</li>
            <li>Schedule your first consultation</li>
        </ol>
        
        <div style="text-align: center; margin: 40px 0;">
            <a href="{{app.url}}/dashboard" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: 600; font-size: 16px; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);">
                Get Started Now 🚀
            </a>
        </div>
        
        <div style="background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 30px 0; text-align: center;">
            <p style="margin: 0; color: #28a745; font-weight: 600;">🎁 Special Welcome Offer!</p>
            <p style="margin: 10px 0 0; color: #666;">Use code <strong>{{custom.promo_code}}</strong> for 20% off your first purchase</p>
        </div>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">If you have any questions, our support team is here to help. Just reply to this email or visit our <a href="{{app.url}}/support" style="color: #007bff;">help center</a>.</p>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">Welcome aboard!<br>
        <strong>The Growth Labs Team</strong></p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; text-align: center; font-size: 14px; color: #999;">
        <p style="margin: 0;">© {{system.year}} {{app.name}}. All rights reserved.</p>
        <p style="margin: 10px 0 0;">
            <a href="{{app.url}}/unsubscribe" style="color: #999;">Unsubscribe</a> | 
            <a href="{{app.url}}/privacy" style="color: #999;">Privacy Policy</a>
        </p>
    </div>
</div>';
    }

    /**
     * Get password reset content
     */
    private function getPasswordResetContent()
    {
        return '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <div style="background: #dc3545; color: white; padding: 30px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">🔒 Password Reset Request</h1>
    </div>
    
    <div style="padding: 40px 20px;">
        <h2 style="color: #333; font-size: 20px; margin-bottom: 20px;">Hi {{user.name}},</h2>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">We received a request to reset your {{app.name}} password. If you made this request, click the button below to reset your password.</p>
        
        <div style="text-align: center; margin: 40px 0;">
            <a href="{{app.url}}/reset-password" style="background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: 600; font-size: 16px;">
                Reset Password
            </a>
        </div>
        
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 8px; margin: 30px 0;">
            <p style="margin: 0; color: #856404; font-weight: 600;">⚠️ Security Notice</p>
            <p style="margin: 10px 0 0; color: #856404;">This link will expire on {{custom.deadline}}. If you didn\'t request this reset, please ignore this email.</p>
        </div>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">For security reasons, this link will expire in 24 hours. If you need help, contact our support team.</p>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">Best regards,<br>
        <strong>The {{app.name}} Security Team</strong></p>
    </div>
</div>';
    }

    /**
     * Get WhatsApp welcome content
     */
    private function getWhatsAppWelcomeContent()
    {
        return '🎉 Welcome to {{app.name}}, {{user.name}}!

Your {{user.plan}} account is now active and ready to use.

✅ Account: {{user.email}}
✅ Plan: {{user.plan}}
✅ Status: Active

🚀 Quick start guide:
1️⃣ Complete your profile
2️⃣ Explore the dashboard
3️⃣ Join our community
4️⃣ Start your first project

🎁 Special offer: Use code {{custom.promo_code}} for 20% off!

Get started: {{app.url}}/dashboard

Need help? Reply HELP
Questions? Reply SUPPORT

Welcome aboard! 🌟';
    }

    /**
     * Get task reminder content
     */
    private function getTaskReminderContent()
    {
        return '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
    <div style="background: #ffc107; color: #212529; padding: 30px 20px; text-align: center;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">⏰ Friendly Reminder</h1>
    </div>
    
    <div style="padding: 40px 20px;">
        <h2 style="color: #333; font-size: 20px; margin-bottom: 20px;">Hi {{user.name}},</h2>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">We noticed you haven\'t completed your profile setup yet. Taking a few minutes to complete your profile will help us provide you with a better experience.</p>
        
        <div style="background: #e3f2fd; padding: 25px; border-radius: 10px; margin: 30px 0; border-left: 4px solid #2196f3;">
            <h3 style="color: #1976d2; margin-top: 0;">Complete these steps:</h3>
            <ul style="color: #666; line-height: 1.8;">
                <li>Add your profile photo</li>
                <li>Set your preferences</li>
                <li>Connect your social accounts</li>
                <li>Verify your phone number</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 40px 0;">
            <a href="{{app.url}}/profile/setup" style="background: #ffc107; color: #212529; padding: 15px 30px; text-decoration: none; border-radius: 25px; display: inline-block; font-weight: 600; font-size: 16px;">
                Complete Profile
            </a>
        </div>
        
        <p style="color: #666; line-height: 1.6; font-size: 14px; text-align: center;">
            <em>Reminder: Please complete by {{custom.deadline}}</em>
        </p>
        
        <p style="color: #666; line-height: 1.6; font-size: 16px;">Thanks for being part of {{app.name}}!</p>
    </div>
</div>';
    }
}
