@extends('layouts.admin')

@section('title', __('notification_templates.demo_title', [], 'Notification Templates Demo'))

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<style>
:root {
    --primary-color: #007bff;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
    --light-bg: #f8f9fa;
    --border-color: #dee2e6;
    --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --shadow-lg: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.demo-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 0;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.demo-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
}

.demo-header h1 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.demo-header p {
    font-size: 1.2rem;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.demo-section {
    padding: 3rem 0;
}

.demo-card {
    background: white;
    border-radius: 15px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.demo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
}

.demo-card-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: white;
    text-align: center;
}

.demo-card-body {
    padding: 2rem;
}

.template-preview {
    background: var(--light-bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    max-height: 300px;
    overflow-y: auto;
}

.template-stats {
    display: flex;
    justify-content: space-around;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.btn-demo {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-demo:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.btn-demo.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    color: white;
}

.btn-demo.btn-success {
    background: linear-gradient(135deg, var(--success-color), #1e7e34);
    color: white;
}

.btn-demo.btn-warning {
    background: linear-gradient(135deg, var(--warning-color), #e0a800);
    color: #212529;
}

.btn-demo.btn-info {
    background: linear-gradient(135deg, var(--info-color), #138496);
    color: white;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.feature-item {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 15px;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    color: white;
}

.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow-lg);
    padding: 1rem;
    transform: translateX(400px);
    transition: all 0.3s ease;
}

.notification-toast.show {
    transform: translateX(0);
}

.notification-toast.success {
    border-left: 4px solid var(--success-color);
}

.notification-toast.error {
    border-left: 4px solid var(--danger-color);
}

.notification-toast.info {
    border-left: 4px solid var(--info-color);
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .demo-header h1 {
        font-size: 2rem;
    }
    
    .demo-header p {
        font-size: 1rem;
    }
    
    .demo-section {
        padding: 2rem 0;
    }
    
    .action-buttons {
        justify-content: stretch;
    }
    
    .btn-demo {
        flex: 1;
        min-width: 120px;
    }
}
</style>
@endsection

@section('content')
<div class="demo-header">
    <div class="container">
        <h1 class="animate__animated animate__fadeInDown">
            <i class="fas fa-rocket me-3"></i>
            {{ __('notification_templates.demo_title', [], 'Notification Templates Demo') }}
        </h1>
        <p class="animate__animated animate__fadeInUp animate__delay-1s">
            {{ __('notification_templates.demo_subtitle', [], 'Experience the power of our improved notification template system') }}
        </p>
    </div>
</div>

<div class="container">
    <!-- Quick Actions Section -->
    <div class="demo-section">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">{{ __('notification_templates.quick_actions', [], 'Quick Actions') }}</h2>
                <div class="action-buttons">
                    <button class="btn btn-demo btn-success" onclick="createSampleTemplates()">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('notification_templates.create_samples', [], 'Create Sample Templates') }}
                    </button>
                    <a href="{{ route('admin.notification-templates.create') }}" class="btn btn-demo btn-primary">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('notification_templates.create_new', [], 'Create New Template') }}
                    </a>
                    <a href="{{ route('admin.notification-templates.index') }}" class="btn btn-demo btn-info">
                        <i class="fas fa-list me-2"></i>
                        {{ __('notification_templates.view_all', [], 'View All Templates') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Templates Section -->
    <div class="demo-section">
        <h2 class="text-center mb-5">{{ __('notification_templates.sample_templates', [], 'Sample Templates') }}</h2>
        
        <div class="row" id="templates-grid">
            @if($templates->count() > 0)
                @foreach($templates as $template)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="demo-card">
                        <div class="demo-card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-{{ $template->channel === 'email' ? 'envelope' : ($template->channel === 'whatsapp' ? 'whatsapp' : 'bell') }} me-2"></i>
                                {{ $template->title }}
                            </h5>
                        </div>
                        <div class="demo-card-body">
                            <div class="template-preview">
                                <strong>{{ __('notification_templates.channel') }}:</strong> {{ ucfirst($template->channel) }}<br>
                                @if($template->subject)
                                    <strong>{{ __('notification_templates.subject') }}:</strong> {{ Str::limit($template->subject, 50) }}<br>
                                @endif
                                <strong>{{ __('notification_templates.content') }}:</strong><br>
                                <div class="mt-2">{{ Str::limit(strip_tags($template->content), 150) }}</div>
                            </div>
                            
                            <div class="template-stats">
                                <div class="stat-item">
                                    <div class="stat-number">{{ count($template->variables ?? []) }}</div>
                                    <div class="stat-label">{{ __('notification_templates.variables') }}</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">{{ $template->channel === 'email' ? Str::wordCount(strip_tags($template->content)) : Str::length($template->content) }}</div>
                                    <div class="stat-label">{{ $template->channel === 'email' ? 'Words' : 'Characters' }}</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <i class="fas fa-{{ $template->is_active ? 'check-circle text-success' : 'times-circle text-danger' }}"></i>
                                    </div>
                                    <div class="stat-label">{{ __('notification_templates.status') }}</div>
                                </div>
                            </div>
                            
                            <div class="action-buttons mt-3">
                                <button class="btn btn-demo btn-primary btn-sm" onclick="previewTemplate({{ $template->id }})">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ __('notification_templates.preview') }}
                                </button>
                                <button class="btn btn-demo btn-success btn-sm" onclick="testTemplate({{ $template->id }})">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    {{ __('notification_templates.test_send') }}
                                </button>
                                <a href="{{ route('admin.notification-templates.edit', $template) }}" class="btn btn-demo btn-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>
                                    {{ __('notification_templates.edit') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="col-12 text-center">
                    <div class="demo-card">
                        <div class="demo-card-body">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h4>{{ __('notification_templates.no_templates', [], 'No Templates Yet') }}</h4>
                            <p class="text-muted">{{ __('notification_templates.create_first_template', [], 'Create your first notification template to get started.') }}</p>
                            <button class="btn btn-demo btn-success" onclick="createSampleTemplates()">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('notification_templates.create_samples', [], 'Create Sample Templates') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Features Section -->
    <div class="demo-section">
        <h2 class="text-center mb-5">{{ __('notification_templates.key_features', [], 'Key Features') }}</h2>
        
        <div class="feature-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h4>{{ __('notification_templates.modern_design', [], 'Modern Design') }}</h4>
                <p>{{ __('notification_templates.modern_design_desc', [], 'Beautiful, responsive templates with modern UI/UX principles') }}</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h4>{{ __('notification_templates.mobile_friendly', [], 'Mobile Friendly') }}</h4>
                <p>{{ __('notification_templates.mobile_friendly_desc', [], 'Optimized for all devices and screen sizes') }}</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h4>{{ __('notification_templates.code_editor', [], 'Advanced Editor') }}</h4>
                <p>{{ __('notification_templates.code_editor_desc', [], 'Powerful CodeMirror editor with syntax highlighting') }}</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <h4>{{ __('notification_templates.live_preview', [], 'Live Preview') }}</h4>
                <p>{{ __('notification_templates.live_preview_desc', [], 'See your changes in real-time as you type') }}</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h4>{{ __('notification_templates.variables', [], 'Dynamic Variables') }}</h4>
                <p>{{ __('notification_templates.variables_desc', [], 'Easy-to-use variable system for personalization') }}</p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h4>{{ __('notification_templates.localization', [], 'Localization') }}</h4>
                <p>{{ __('notification_templates.localization_desc', [], 'Full internationalization support') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Test Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane me-2"></i>
                    {{ __('notification_templates.test_template', [], 'Test Template') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testForm">
                    <div class="mb-3">
                        <label for="testEmail" class="form-label">{{ __('notification_templates.test_email_label', [], 'Test Email Address') }}</label>
                        <input type="email" class="form-control" id="testEmail" required 
                               placeholder="{{ __('notification_templates.test_email_placeholder', [], 'Enter email to receive test notification') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('notification_templates.template_preview', [], 'Template Preview') }}</label>
                        <div id="templatePreview" class="border p-3 bg-light rounded"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('notification_templates.cancel', [], 'Cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="sendTest()">
                    <i class="fas fa-paper-plane me-1"></i>
                    {{ __('notification_templates.send_test', [], 'Send Test') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    {{ __('notification_templates.template_preview', [], 'Template Preview') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fullPreview"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('notification_templates.close', [], 'Close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentTemplateId = null;

function createSampleTemplates() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="loading-spinner me-2"></span>{{ __('notification_templates.creating', [], 'Creating...') }}';
    
    fetch('/admin/notification-templates/demo/create-samples', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('{{ __('notification_templates.error_occurred', [], 'An error occurred') }}', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function testTemplate(templateId) {
    currentTemplateId = templateId;
    
    // Load template preview
    fetch(`/admin/notification-templates/demo/${templateId}/preview`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            data: @json($demoData)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('templatePreview').innerHTML = `
                <strong>{{ __('notification_templates.channel') }}:</strong> ${data.template.channel}<br>
                ${data.template.subject ? '<strong>{{ __('notification_templates.subject') }}:</strong> ' + data.template.subject + '<br>' : ''}
                <strong>{{ __('notification_templates.content') }}:</strong><br>
                <div class="mt-2">${data.preview.content || data.template.content}</div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('testModal'));
            modal.show();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('{{ __('notification_templates.error_occurred', [], 'Failed to load template preview') }}', 'error');
        console.error('Error:', error);
    });
}

function sendTest() {
    const email = document.getElementById('testEmail').value;
    if (!email) {
        showNotification('{{ __('notification_templates.test_email_required', [], 'Please enter a test email address') }}', 'error');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="loading-spinner me-2"></span>{{ __('notification_templates.sending', [], 'Sending...') }}';
    
    fetch('/admin/notification-templates/demo/send-test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            template_id: currentTemplateId,
            recipient: email,
            test_data: @json($demoData)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById('testModal'));
            modal.hide();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('{{ __('notification_templates.error_occurred', [], 'Failed to send test notification') }}', 'error');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function previewTemplate(templateId) {
    fetch(`/admin/notification-templates/demo/${templateId}/preview`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            data: @json($demoData)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('fullPreview').innerHTML = data.preview.content || data.template.content;
            
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('{{ __('notification_templates.error_occurred', [], 'Failed to load template preview') }}', 'error');
        console.error('Error:', error);
    });
}

function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `notification-toast ${type}`;
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, 5000);
}

// Initialize tooltips and other Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
