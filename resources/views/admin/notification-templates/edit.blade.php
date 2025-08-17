@extends('layouts.admin')

@section('title', __('notification_templates.edit_title', [], 'Edit Notification Template'))

@section('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material.min.css">
<style>
    /* Page Layout */
    .page-header {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 1.5rem 0;
        margin-bottom: 2rem;
    }
    
    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }
    
    .page-subtitle {
        color: #6c757d;
        font-size: 0.95rem;
        margin-top: 0.25rem;
    }
    
    .content-wrapper {
        background: #f8f9fa;
        min-height: calc(100vh - 200px);
        padding: 0;
    }
    
    /* Form Cards */
    .form-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .form-card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    
    .form-card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-card-body {
        padding: 1.5rem;
    }
    
    /* Form Elements */
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .required {
        color: #dc3545;
    }
    
    .form-control {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.75rem;
        font-size: 0.95rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    /* Editor */
    .editor-container {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .editor-tabs {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        display: flex;
    }
    
    .editor-tab {
        background: none;
        border: none;
        padding: 0.75rem 1rem;
        color: #6c757d;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.15s ease;
    }
    
    .editor-tab.active {
        color: #007bff;
        border-bottom-color: #007bff;
        background: #fff;
    }
    
    .tab-content {
        padding: 1rem;
        min-height: 300px;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .preview-panel {
        background: #f8f9fa;
        border-radius: 0.375rem;
        padding: 1rem;
        min-height: 250px;
    }
    
    /* Variables Panel */
    .variables-panel {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        background: #fff;
    }
    
    .variables-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 0.75rem 1rem;
        font-weight: 600;
        color: #495057;
        border-radius: 0.5rem 0.5rem 0 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .variables-content {
        padding: 1rem;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .variable-item {
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        margin: 0.25rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        display: inline-block;
        font-size: 0.875rem;
        transition: all 0.15s ease;
    }
    
    .variable-item:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    
    /* Buttons */
    .btn-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        padding: 1.5rem 0;
    }
    
    .CodeMirror {
        height: 300px;
        border: none;
        font-size: 14px;
    }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('notification_templates.edit_title', [], 'Edit Notification Template') }}
                </h1>
                <p class="page-subtitle mb-0">
                    {{ __('notification_templates.edit_subtitle', [], 'Modify notification template settings and content') }}
                </p>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.notification-templates.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('notification_templates.back_to_templates', [], 'Back to Templates') }}
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="container-fluid">
                
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ __('notification_templates.validation_errors', [], 'Please correct the following errors:') }}
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <form id="template-form" action="{{ route('admin.notification-templates.update', $notificationTemplate) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Basic Information Card -->
            <div class="form-card mb-4">
                <div class="form-card-header">
                    <h5 class="form-card-title">
                        <i class="fas fa-info-circle"></i>
                        {{ __('notification_templates.basic_info', [], 'Basic Information') }}
                    </h5>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    {{ __('notification_templates.template_name', [], 'Template Name') }}
                                    <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $notificationTemplate->name) }}" 
                                       required 
                                       placeholder="{{ __('notification_templates.name_placeholder', [], 'e.g., welcome-email') }}">
                                <div class="help-text">
                                    {{ __('notification_templates.name_help', [], 'Unique identifier for this template') }}
                                </div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title" class="form-label">
                                    {{ __('notification_templates.template_title', [], 'Template Title') }}
                                    <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title', $notificationTemplate->title) }}" 
                                       required
                                       placeholder="{{ __('notification_templates.title_placeholder', [], 'e.g., Welcome Email') }}">
                                <div class="help-text">
                                    {{ __('notification_templates.title_help', [], 'Human-readable title') }}
                                </div>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">
                            {{ __('notification_templates.description', [], 'Description') }}
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="{{ __('notification_templates.description_placeholder', [], 'Brief description of this template...') }}">{{ old('description', $notificationTemplate->description) }}</textarea>
                        <div class="help-text">
                            {{ __('notification_templates.description_help', [], 'Optional description for this template') }}
                        </div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if($notificationTemplate->parent_template_id)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('notification_templates.template_version_info', [], 'This template is a clone/version of another template') }} (v{{ $notificationTemplate->version }})
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Channel Configuration Card -->
            <div class="form-card mb-4">
                <div class="form-card-header">
                    <h5 class="form-card-title">
                        <i class="fas fa-broadcast-tower"></i>
                        {{ __('notification_templates.channel_config', [], 'Channel Configuration') }}
                    </h5>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="channel" class="form-label">
                                    {{ __('notification_templates.channel', [], 'Channel') }}
                                    <span class="required">*</span>
                                </label>
                                <select class="form-control @error('channel') is-invalid @enderror" 
                                        id="channel" 
                                        name="channel" 
                                        required>
                                    <option value="">{{ __('notification_templates.select_channel', [], 'Select Channel') }}</option>
                                    @foreach($channels as $channel)
                                        <option value="{{ $channel }}" {{ old('channel', $notificationTemplate->channel) == $channel ? 'selected' : '' }}>
                                            {{ ucfirst($channel) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="help-text">
                                    {{ __('notification_templates.channel_help', [], 'Select the notification channel') }}
                                </div>
                                @error('channel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group" id="sender-group" style="display: none;">
                                <label for="sender" class="form-label">
                                    {{ __('notification_templates.sender', [], 'Sender') }}
                                </label>
                                <input type="text" 
                                       class="form-control @error('sender') is-invalid @enderror" 
                                       id="sender" 
                                       name="sender" 
                                       value="{{ old('sender', $notificationTemplate->sender) }}"
                                       placeholder="{{ __('notification_templates.sender_placeholder', [], 'e.g., noreply@company.com') }}">
                                <div class="help-text">
                                    {{ __('notification_templates.sender_help', [], 'Sender information') }}
                                </div>
                                @error('sender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" id="subject-group" style="display: none;">
                        <label for="subject" class="form-label">
                            {{ __('notification_templates.subject', [], 'Subject') }}
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" 
                               name="subject" 
                               value="{{ old('subject', $notificationTemplate->subject) }}"
                               placeholder="{{ __('notification_templates.subject_placeholder', [], 'e.g., Welcome to {app.name}!') }}">
                        <div class="help-text">
                            {{ __('notification_templates.subject_help', [], 'Email subject line') }}
                        </div>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $notificationTemplate->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                {{ __('notification_templates.active_template', [], 'Active Template') }}
                            </label>
                        </div>
                        <div class="help-text">
                            {{ __('notification_templates.active_help', [], 'Enable this template for use') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Content Card -->
            <div class="form-card mb-4">
                <div class="form-card-header">
                    <h5 class="form-card-title">
                        <i class="fas fa-edit"></i>
                        {{ __('notification_templates.template_content', [], 'Template Content') }}
                    </h5>
                </div>
                <div class="form-card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="editor-container">
                                <div class="editor-tabs">
                                    <button type="button" class="editor-tab active" data-tab="editor">
                                        <i class="fas fa-code me-1"></i>
                                        {{ __('notification_templates.editor', [], 'Editor') }}
                                    </button>
                                    <button type="button" class="editor-tab" data-tab="preview">
                                        <i class="fas fa-eye me-1"></i>
                                        {{ __('notification_templates.preview', [], 'Preview') }}
                                    </button>
                                </div>
                                
                                <div id="editor-content" class="tab-content active">
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" 
                                              name="content" 
                                              required
                                              placeholder="{{ __('notification_templates.content_placeholder', [], 'Enter your template content here...') }}">{{ old('content', $notificationTemplate->content) }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div id="preview-content" class="tab-content preview-panel" style="display: none;">
                                    <div id="preview-body">
                                        <p class="text-muted">{{ __('notification_templates.preview_empty', [], 'Start typing to see preview...') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="help-text mt-2">
                                {{ __('notification_templates.content_help', [], 'Use {variable} for dynamic content') }}
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="variables-panel">
                                <div class="variables-header">
                                    <i class="fas fa-tags me-1"></i>
                                    {{ __('notification_templates.available_variables', [], 'Available Variables') }}
                                </div>
                                <div class="variables-content">
                                    <div id="variables-container">
                                        <div class="text-center py-3">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted mb-0 mt-2">{{ __('notification_templates.loading_variables', [], 'Loading variables...') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="btn-toolbar">
                <div>
                    <a href="{{ route('admin.notification-templates.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('notification_templates.back_to_templates', [], 'Back') }}
                    </a>
                </div>
                
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" id="test-template-btn">
                        <i class="fas fa-paper-plane me-1"></i>
                        {{ __('notification_templates.test_send', [], 'Test Send') }}
                    </button>
                    
                    <button type="submit" class="btn btn-primary" id="save-template-btn">
                        <i class="fas fa-save me-1"></i>
                        {{ __('notification_templates.update_template', [], 'Update Template') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Test Send Modal -->
<div class="modal fade" id="testSendModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Send Notification</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="test-send-form">
                <div class="modal-body">
                    <div class="form-group" id="test-email-group">
                        <label for="test-email">Test Email Address</label>
                        <input type="email" class="form-control" id="test-email" name="test_email">
                    </div>
                    <div class="form-group" id="test-phone-group" style="display: none;">
                        <label for="test-phone">Test Phone Number</label>
                        <input type="text" class="form-control" id="test-phone" name="test_phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Test</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
let editor;
let sampleData = @json($sampleData);
let templateId = {{ $notificationTemplate->id }};
// Precomputed URLs to avoid path issues
const urls = {
    preview: "{{ route('admin.notification-templates.preview', $notificationTemplate) }}",
    testSend: "{{ route('admin.notification-templates.test-send', $notificationTemplate) }}",
    variables: "{{ route('admin.notification-templates.variables') }}"
};

$(document).ready(function() {
    // Initialize CodeMirror editor
    editor = CodeMirror.fromTextArea(document.getElementById('content'), {
        mode: 'htmlmixed',
        theme: 'material',
        lineNumbers: true,
        lineWrapping: true,
        autoCloseTags: true,
        autoCloseBrackets: true
    });

    // Load available variables
    loadVariables();

    // Channel change handler
    $('#channel').change(function() {
        const channel = $(this).val();
        if (channel === 'email') {
            $('#subject-group').show();
            $('#subject').attr('required', true);
        } else {
            $('#subject-group').hide();
            $('#subject').removeAttr('required');
        }
        updateTestSendModal(channel);
    });

    // Trigger channel change on load
    $('#channel').trigger('change');

    // Preview refresh
    $('#refresh-preview').click(function() {
        refreshPreview();
    });

    // Auto-refresh preview on content change
    editor.on('change', function() {
        clearTimeout(window.previewTimeout);
        window.previewTimeout = setTimeout(refreshPreview, 1000);
    });

    // Initial preview
    refreshPreview();

    // Preview button
    $('#preview-btn').click(function() {
        let content = '';
        try {
            content = editor && typeof editor.getValue === 'function' ? editor.getValue() : ($('#content').val() || '');
        } catch (e) {
            content = $('#content').val() || '';
        }
        const data = {
            content: content,
            channel: $('#channel').val() || null,
            subject: $('#subject').val() || null
        };

        $.ajax({
            url: urls.preview,
            method: 'GET',
            data: data,
            headers: { 'Accept': 'application/json' }
        })
        .done(function(response) {
            const rendered = (response && response.rendered_content) ? response.rendered_content : '';
            $('#preview-content').html(`
                <div class="alert alert-success">
                    <small><i class="fas fa-check-circle"></i> Server-side preview with sample data</small>
                </div>
                <div class="preview-content">${String(rendered).replace(/\n/g, '<br>')}</div>
            `);
        })
        .fail(function(xhr) {
            let msg = 'Failed to load server preview';
            if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) {
                msg += ': ' + (xhr.responseJSON.message || xhr.responseJSON.error);
            }
            // Fallback to client-side preview with sample data
            try {
                let local = content || '';
                if (!local.trim()) {
                    $('#preview-content').html(`<div class="alert alert-danger mb-2"><small>${msg}</small></div>`);
                    return;
                }
                const defaultSampleData = {
                    user: { name: 'John Doe', email: 'john.doe@example.com', first_name: 'John', last_name: 'Doe' },
                    company: { name: 'Acme Corporation', website: 'https://acme.com' },
                    order: { id: '#12345', total: '$299.99', date: '2024-08-17' },
                    system: { app_name: 'VI Portal', support_email: 'support@viportal.com', date: new Date().toLocaleDateString() }
                };
                const previewData = typeof sampleData !== 'undefined' && sampleData ? sampleData : defaultSampleData;
                function replaceVariables(text, data, prefix = '') {
                    Object.keys(data).forEach(key => {
                        const fullKey = prefix ? prefix + '.' + key : key;
                        if (typeof data[key] === 'object' && data[key] !== null) {
                            text = replaceVariables(text, data[key], fullKey);
                        } else {
                            const regex = new RegExp('\\\\{\\\\{' + fullKey.replace(/[.*+?^${}()|[\\]\\\\]/g, '\\$&') + '\\\\}\\\\}', 'g');
                            text = text.replace(regex, data[key]);
                        }
                    });
                    return text;
                }
                local = replaceVariables(local, previewData).replace(/\n/g, '<br>');
                $('#preview-content').html(`
                    <div class="alert alert-warning mb-2"><small>${msg}. Showing client-side preview with sample data.</small></div>
                    <div class="preview-content">${local}</div>
                `);
            } catch (e) {
                $('#preview-content').html(`<div class="alert alert-danger mb-2"><small>${msg}</small></div>`);
            }
        });
    });

    // Test send functionality
    $('#test-template-btn').click(function() {
        $('#testSendModal').modal('show');
    });

    $('#test-send-form').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            test_email: $('#test-email').val(),
            test_phone: $('#test-phone').val()
        };
        
        $.post(urls.testSend, formData)
            .done(function(response) {
                $('#testSendModal').modal('hide');
                alert('Test notification sent successfully!');
            })
            .fail(function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    alert('Error: ' + xhr.responseJSON.error);
                } else {
                    alert('Failed to send test notification');
                }
            });
    });
});

function loadVariables() {
    $.get(urls.variables)
        .done(function(variables) {
            let html = '';
            Object.keys(variables).forEach(category => {
                html += `<div class="mb-3">
                    <h6 class="text-primary">${category.charAt(0).toUpperCase() + category.slice(1)}</h6>
                    <div class="variables-group">`;
                
                Object.keys(variables[category]).forEach(key => {
                    const fullKey = `${category}.${key}`;
                    // Build literal Blade-like braces without triggering Blade
                    const open = '{' + '{';
                    const close = '}' + '}';
                    const displayText = open + fullKey + close;
                    html += `<button type="button" class="variable-item btn btn-sm btn-outline-primary me-2 mb-2" 
                        data-variable="${fullKey}" 
                        data-display-text="${displayText}"
                        title="${variables[category][key]}">${displayText}</button>`;
                });
                
                html += `</div></div>`;
            });
            
            $('#variables-container').html(html);
            
            // Add click handlers for variables
            $('.variable-item').click(function() {
                // Compose literal double-brace placeholders without Blade parsing
                const variable = $(this).data('variable');
                const variableText = '{' + '{' + variable + '}' + '}';
                const cursor = editor.getCursor();
                editor.replaceRange(variableText, cursor);
                editor.focus();
                refreshPreview();
            });
        })
        .fail(function() {
            $('#variables-container').html('<p class="text-danger">Failed to load variables.</p>');
        });
}

function refreshPreview() {
    const content = editor.getValue();
    if (!content.trim()) {
        $('#preview-content').html('<p class="text-muted">Enter template content to see preview...</p>');
        return;
    }

    // Simple client-side preview with sample data
    let preview = content;
    
    // Replace variables with sample data
    function replaceVariables(text, data, prefix = '') {
        Object.keys(data).forEach(key => {
            const fullKey = prefix ? `${prefix}.${key}` : key;
            if (typeof data[key] === 'object' && data[key] !== null) {
                text = replaceVariables(text, data[key], fullKey);
            } else {
                const regex = new RegExp(`\\{\\{${fullKey}\\}\\}`, 'g');
                text = text.replace(regex, data[key]);
            }
        });
        return text;
    }
    
    preview = replaceVariables(preview, sampleData);
    
    // Convert line breaks to HTML
    preview = preview.replace(/\n/g, '<br>');
    
    $('#preview-content').html(`
        <div class="alert alert-info">
            <small><i class="fas fa-info-circle"></i> Live preview using sample data</small>
        </div>
        <div class="preview-content">${preview}</div>
    `);
}

function updateTestSendModal(channel) {
    $('#test-email-group, #test-phone-group').hide();
    
    if (channel === 'email') {
        $('#test-email-group').show();
        $('#test-email').attr('required', true);
        $('#test-phone').removeAttr('required');
    } else if (channel === 'whatsapp') {
        $('#test-phone-group').show();
        $('#test-phone').attr('required', true);
        $('#test-email').removeAttr('required');
    } else {
        $('#test-email, #test-phone').removeAttr('required');
    }
}
</script>
@endsection
