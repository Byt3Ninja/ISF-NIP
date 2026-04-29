@php
    $setting = App\Settings::first();
    $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
@endphp

@extends('layouts.admin')

@section('title', __('notification_templates.example_title', [], 'Example Notification Template'))

@section('content')
<div class="notification-template-form">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="form-container">
                    <div class="form-header">
                        <h1>
                            <i class="fas fa-lightbulb me-2"></i>
                            {{ __('notification_templates.example_title', [], 'Example: Welcome Email Template') }}
                        </h1>
                        <p>{{ __('notification_templates.example_subtitle', [], 'A complete example showing best practices for notification templates') }}</p>
                    </div>
                    
                    <div class="form-body">
                        <!-- Example Template Preview -->
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-eye"></i>
                                {{ __('notification_templates.template_preview', [], 'Template Preview') }}
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-envelope me-2"></i>
                                                Email Template
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="email-preview">
                                                <div class="email-header mb-3">
                                                    <strong>From:</strong> welcome@growthlabs.com<br>
                                                    <strong>To:</strong> {{user.email}}<br>
                                                    <strong>Subject:</strong> Welcome to {{app.name}}, {{user.name}}!
                                                </div>
                                                <div class="email-content">
                                                    <h2 style="color: #007bff;">Welcome to Growth Labs!</h2>
                                                    <p>Hi {{user.name}},</p>
                                                    <p>We're thrilled to have you join our community! Your account has been successfully created and you're now ready to start your journey with us.</p>
                                                    
                                                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                                                        <h4>Your Account Details:</h4>
                                                        <ul>
                                                            <li><strong>Email:</strong> {{user.email}}</li>
                                                            <li><strong>Account Type:</strong> {{user.plan}}</li>
                                                            <li><strong>Member Since:</strong> {{user.created_at}}</li>
                                                        </ul>
                                                    </div>
                                                    
                                                    <p>Here are some quick steps to get you started:</p>
                                                    <ol>
                                                        <li>Complete your profile setup</li>
                                                        <li>Explore our dashboard features</li>
                                                        <li>Join our community forum</li>
                                                        <li>Schedule your first consultation</li>
                                                    </ol>
                                                    
                                                    <div style="text-align: center; margin: 30px 0;">
                                                        <a href="{{app.url}}/dashboard" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block;">
                                                            Get Started Now
                                                        </a>
                                                    </div>
                                                    
                                                    <p>If you have any questions, our support team is here to help. Just reply to this email or visit our <a href="{{app.url}}/support">help center</a>.</p>
                                                    
                                                    <p>Welcome aboard!<br>
                                                    The Growth Labs Team</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">
                                                <i class="fab fa-whatsapp me-2"></i>
                                                WhatsApp Template
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="whatsapp-preview">
                                                <div class="whatsapp-message">
                                                    <div class="message-bubble">
                                                        🎉 Welcome to Growth Labs, {{user.name}}!
                                                        
                                                        Your account is now active and ready to use.
                                                        
                                                        ✅ Account: {{user.email}}
                                                        ✅ Plan: {{user.plan}}
                                                        ✅ Status: Active
                                                        
                                                        Quick start guide:
                                                        1️⃣ Complete your profile
                                                        2️⃣ Explore the dashboard
                                                        3️⃣ Join our community
                                                        
                                                        Get started: {{app.url}}/dashboard
                                                        
                                                        Need help? Reply HELP
                                                        
                                                        Welcome aboard! 🚀
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Template Configuration -->
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-cogs"></i>
                                {{ __('notification_templates.template_config', [], 'Template Configuration') }}
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="config-item">
                                        <h6>Email Template</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Name:</strong> welcome-email</li>
                                            <li><strong>Channel:</strong> Email</li>
                                            <li><strong>Subject:</strong> Welcome to {{app.name}}, {{user.name}}!</li>
                                            <li><strong>Sender:</strong> welcome@growthlabs.com</li>
                                            <li><strong>Variables:</strong> 8 used</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="config-item">
                                        <h6>WhatsApp Template</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Name:</strong> welcome-whatsapp</li>
                                            <li><strong>Channel:</strong> WhatsApp</li>
                                            <li><strong>Sender:</strong> Growth Labs</li>
                                            <li><strong>Length:</strong> 245 characters</li>
                                            <li><strong>Variables:</strong> 5 used</li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="config-item">
                                        <h6>Best Practices</h6>
                                        <ul class="list-unstyled text-success">
                                            <li><i class="fas fa-check me-1"></i> Personalized greeting</li>
                                            <li><i class="fas fa-check me-1"></i> Clear call-to-action</li>
                                            <li><i class="fas fa-check me-1"></i> Branded design</li>
                                            <li><i class="fas fa-check me-1"></i> Mobile-friendly</li>
                                            <li><i class="fas fa-check me-1"></i> Accessible content</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Available Variables -->
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-tags"></i>
                                {{ __('notification_templates.available_variables', [], 'Available Variables') }}
                            </h3>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="variable-category">
                                        <h6>User Variables</h6>
                                        <div class="variables-list">
                                            <span class="variable-item">user.name</span>
                                            <span class="variable-item">user.email</span>
                                            <span class="variable-item">user.plan</span>
                                            <span class="variable-item">user.created_at</span>
                                            <span class="variable-item">user.phone</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="variable-category">
                                        <h6>App Variables</h6>
                                        <div class="variables-list">
                                            <span class="variable-item">app.name</span>
                                            <span class="variable-item">app.url</span>
                                            <span class="variable-item">app.logo</span>
                                            <span class="variable-item">app.support_email</span>
                                            <span class="variable-item">app.phone</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="variable-category">
                                        <h6>System Variables</h6>
                                        <div class="variables-list">
                                            <span class="variable-item">date</span>
                                            <span class="variable-item">time</span>
                                            <span class="variable-item">timestamp</span>
                                            <span class="variable-item">year</span>
                                            <span class="variable-item">month</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="variable-category">
                                        <h6>Custom Variables</h6>
                                        <div class="variables-list">
                                            <span class="variable-item">company.name</span>
                                            <span class="variable-item">company.address</span>
                                            <span class="variable-item">promo.code</span>
                                            <span class="variable-item">discount.amount</span>
                                            <span class="variable-item">event.name</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Implementation Guide -->
                        <div class="form-section">
                            <h3>
                                <i class="fas fa-code"></i>
                                {{ __('notification_templates.implementation_guide', [], 'Implementation Guide') }}
                            </h3>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="code-example">
                                        <h6>Laravel Controller Usage:</h6>
                                        <pre><code class="language-php">// Send welcome email
use App\Services\NotificationTemplateService;

$notificationService = new NotificationTemplateService();

$notificationService->send('welcome-email', [
    'user' => $user,
    'app' => [
        'name' => $platformName,
        'url' => config('app.url'),
    ],
    'company' => [
        'name' => 'Growth Labs',
        'address' => '123 Innovation St, Tech City'
    ]
], $user->email);</code></pre>
                                    </div>
                                    
                                    <div class="code-example mt-4">
                                        <h6>JavaScript Frontend Usage:</h6>
                                        <pre><code class="language-javascript">// Send notification via API
fetch('/api/notifications/send', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        template: 'welcome-email',
        recipient: user.email,
        variables: {
            user: user,
            app: appConfig,
            company: companyInfo
        }
    })
});</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('notification-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ __('notification_templates.back_to_templates', [], 'Back to Templates') }}
                            </a>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('notification-templates.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('notification_templates.create_template', [], 'Create New Template') }}
                                </a>
                                
                                <button type="button" class="btn btn-primary" onclick="copyExampleTemplate()">
                                    <i class="fas fa-copy me-1"></i>
                                    {{ __('notification_templates.copy_example', [], 'Copy This Example') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.email-preview {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
}

.email-header {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    font-size: 0.9rem;
    border-left: 4px solid #007bff;
}

.whatsapp-preview {
    background: #e5ddd5;
    padding: 20px;
    border-radius: 10px;
    min-height: 300px;
}

.whatsapp-message {
    display: flex;
    justify-content: flex-end;
}

.message-bubble {
    background: #dcf8c6;
    padding: 12px 16px;
    border-radius: 18px;
    max-width: 85%;
    font-size: 14px;
    line-height: 1.4;
    white-space: pre-line;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.config-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    height: 100%;
    border-left: 4px solid #28a745;
}

.config-item h6 {
    color: #28a745;
    font-weight: 600;
    margin-bottom: 15px;
}

.config-item ul li {
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.code-example {
    background: #2d3748;
    color: #e2e8f0;
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
}

.code-example h6 {
    color: #63b3ed;
    margin-bottom: 15px;
}

.code-example pre {
    margin: 0;
    background: transparent;
    border: none;
    padding: 0;
    color: inherit;
}

.code-example code {
    color: inherit;
    font-size: 0.9rem;
    line-height: 1.5;
}

.variable-item {
    display: inline-block;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border: 1px solid #2196f3;
    color: #1565c0;
    padding: 0.4rem 0.8rem;
    margin: 0.2rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: default;
    user-select: none;
}
</style>
@endsection

@section('scripts')
<script>
function copyExampleTemplate() {
    // This would copy the example template configuration
    // In a real implementation, this would make an API call to create the template
    
    const templateData = {
        name: 'welcome-email-example',
        title: 'Welcome Email (Example)',
        description: 'A comprehensive welcome email template with best practices',
        channel: 'email',
        subject: 'Welcome to {{app.name}}, {{user.name}}!',
        sender: 'welcome@growthlabs.com',
        content: `<h2 style="color: #007bff;">Welcome to Growth Labs!</h2>
<p>Hi {{user.name}},</p>
<p>We're thrilled to have you join our community! Your account has been successfully created and you're now ready to start your journey with us.</p>

<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h4>Your Account Details:</h4>
    <ul>
        <li><strong>Email:</strong> {{user.email}}</li>
        <li><strong>Account Type:</strong> {{user.plan}}</li>
        <li><strong>Member Since:</strong> {{user.created_at}}</li>
    </ul>
</div>

<p>Here are some quick steps to get you started:</p>
<ol>
    <li>Complete your profile setup</li>
    <li>Explore our dashboard features</li>
    <li>Join our community forum</li>
    <li>Schedule your first consultation</li>
</ol>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{app.url}}/dashboard" style="background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; display: inline-block;">
        Get Started Now
    </a>
</div>

<p>If you have any questions, our support team is here to help. Just reply to this email or visit our <a href="{{app.url}}/support">help center</a>.</p>

<p>Welcome aboard!<br>
The Growth Labs Team</p>`
    };
    
    // Store in localStorage for the create form
    localStorage.setItem('template_example_data', JSON.stringify(templateData));
    
    // Redirect to create form
    window.location.href = '{{ route("notification-templates.create") }}?example=welcome';
}
</script>
@endsection
