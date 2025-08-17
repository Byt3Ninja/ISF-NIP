@extends('layouts.admin')

@section('title', __('notification_templates.create_title'))

@section('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material-darker.min.css">
<style>
.page-header {
    background: #fff;
    padding: 1.5rem 0;
    margin-bottom: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.page-subtitle {
    color: #6c757d;
    margin-top: 0.25rem;
    font-size: 0.95rem;
}

.content-wrapper {
    background: #f8f9fa;
    min-height: calc(100vh - 200px);
    padding: 2rem 0;
}

.form-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid #e9ecef;
}

.form-card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.form-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-card-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.required {
    color: #dc3545;
}

.form-control {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Editor improvements */
.CodeMirror {
    min-height: 280px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 0.95rem;
}
.editor-tabs {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.editor-tab {
    border: 1px solid #dee2e6;
    background: #fff;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    cursor: pointer;
}
.editor-tab.active {
    background: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: #dc3545;
}

.help-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.editor-container {
    border: 1px solid #ced4da;
    border-radius: 4px;
    overflow: hidden;
}

.editor-tabs {
    background: #f8f9fa;
    border-bottom: 1px solid #ced4da;
    display: flex;
    padding: 0;
    margin: 0;
}

.editor-tab {
    background: none;
    border: none;
    padding: 0.75rem 1rem;
    color: #6c757d;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.15s ease;
}

.editor-tab:hover {
    background: #e9ecef;
    color: #495057;
}

.editor-tab.active {
    background: #fff;
    color: #007bff;
    border-bottom: 2px solid #007bff;
}

.CodeMirror {
    height: 300px;
    font-size: 13px;
    line-height: 1.4;
    border: none;
}

.variables-panel {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    max-height: 400px;
    overflow-y: auto;
}

.variables-header {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e9ecef;
    font-weight: 500;
    font-size: 0.9rem;
    color: #495057;
}

.variables-content {
    padding: 1rem;
}

.variable-category {
    margin-bottom: 1rem;
}

.variable-category:last-child {
    margin-bottom: 0;
}

.variable-category h6 {
    font-size: 0.8rem;
    font-weight: 600;
    color: #007bff;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.variable-item {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.5rem;
    margin: 0.125rem;
    border-radius: 3px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid #bbdefb;
}

.variable-item:hover {
    background: #1976d2;
    color: #fff;
}

.preview-panel {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 1rem;
    min-height: 250px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 0.9rem;
    line-height: 1.5;
}

.btn-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 8px 8px;
}

.btn {
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    transition: all 0.15s ease;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #545b62;
    border-color: #545b62;
}

.btn-outline-primary {
    color: #007bff;
    border-color: #007bff;
}

.btn-outline-primary:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
}

.alert {
    border-radius: 4px;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

@media (max-width: 768px) {
    .content-wrapper {
        padding: 1rem 0;
    }
    
    .form-card-body {
        padding: 1rem;
    }
    
    .btn-toolbar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .btn-toolbar .btn {
        width: 100%;
    }
    
    .variables-panel {
        margin-top: 1rem;
        max-height: 250px;
    }
    
    .CodeMirror {
        height: 200px;
    }
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">
                    <i class="fas fa-plus-circle me-2"></i>
                    {{ __('notification_templates.create_title') }}
                </h1>
                <p class="page-subtitle mb-0">
                    {{ __('notification_templates.create_subtitle', [], 'Create a new notification template') }}
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
        
        <form id="template-form" action="{{ route('admin.notification-templates.store') }}" method="POST">
            @csrf
            
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
                                       value="{{ old('name') }}" 
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
                                       value="{{ old('title') }}" 
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
                                  placeholder="{{ __('notification_templates.description_placeholder', [], 'Brief description of this template...') }}">{{ old('description') }}</textarea>
                        <div class="help-text">
                            {{ __('notification_templates.description_help', [], 'Optional description for this template') }}
                        </div>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
                                        <option value="{{ $channel }}" {{ old('channel') == $channel ? 'selected' : '' }}>
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
                                       value="{{ old('sender') }}"
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
                               value="{{ old('subject') }}"
                               placeholder="{{ __('notification_templates.subject_placeholder', [], 'e.g., Welcome to {app.name}!') }}">
                        <div class="help-text">
                            {{ __('notification_templates.subject_help', [], 'Email subject line') }}
                        </div>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                                              placeholder="{{ __('notification_templates.content_placeholder', [], 'Enter your template content here...') }}">{{ old('content') }}</textarea>
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
                        {{ __('notification_templates.save_template', [], 'Save Template') }}
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
let isFormSubmitting = false;

$(document).ready(function() {
    // Initialize CodeMirror with better theme
    if (typeof CodeMirror !== 'undefined' && document.getElementById('content')) {
        editor = CodeMirror.fromTextArea(document.getElementById('content'), {
            mode: 'htmlmixed',
            theme: 'material-darker',
            lineNumbers: true,
            autoCloseTags: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 2,
            tabSize: 2,
            lineWrapping: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "Ctrl-S": function() { $('#template-form').submit(); }
            }
        });
        
        editor.on('change', function() {
            editor.save();
            refreshPreview();
        });
    } else {
        $('#content').on('input', refreshPreview);
    }
    
    loadVariables();
    
    // Enhanced channel change handler
    $('#channel').on('change', function() {
        const channel = $(this).val();
        $('#subject-group, #sender-group').hide();
        $('#subject').prop('required', false);
        
        if (channel === 'email') {
            $('#subject-group, #sender-group').show();
            $('#subject').prop('required', true);
        } else if (channel === 'whatsapp') {
            $('#sender-group').show();
        }
    }).trigger('change');
    
    // Enhanced form submission with loading state
    $('#template-form').on('submit', function(e) {
        if (isFormSubmitting) {
            e.preventDefault();
            return false;
        }
        
        if (editor) editor.save();
        
        if (!validateForm()) {
            e.preventDefault();
            showNotification('{{ __('notification_templates.validation_failed', [], 'Please fill in all required fields.') }}', 'error');
            return false;
        }
        
        isFormSubmitting = true;
        showLoadingState();
    });
    
    // Enhanced test functionality
    $('#test-template-btn').on('click', function() {
        if (editor) editor.save();
        
        if (!validateForm()) {
            showNotification('{{ __('notification_templates.test_validation_failed', [], 'Please complete the form before testing.') }}', 'error');
            return;
        }
        
        showTestModal(getFormData());
    });
    
    // Tab switching
    $('.editor-tab').on('click', function() {
        const tab = $(this).data('tab');
        $('.editor-tab').removeClass('active');
        $(this).addClass('active');
        
        if (tab === 'editor') {
            $('#editor-content').show();
            $('#preview-content').hide();
            if (editor) editor.refresh();
        } else {
            $('#editor-content').hide();
            $('#preview-content').show();
            refreshPreview();
        }
    });
});

function loadVariables() {
    console.log('Loading variables...');
    $.get('{{ route("admin.notification-templates.variables") }}')
        .done(function(variables) {
            console.log('Variables received:', variables);
            
            // Completely replace the variables container with new content
            var newContainer = $('<div id="variables-container"></div>');
            
            // Build variables HTML using simple approach
            Object.keys(variables).forEach(function(category) {
                console.log('Processing category:', category);
                
                // Create category header
                var categoryHeader = $('<h6 class="text-primary mb-2"></h6>').text(category.charAt(0).toUpperCase() + category.slice(1));
                newContainer.append(categoryHeader);
                
                // Create variables for this category
                var variablesRow = $('<div class="mb-3"></div>');
                
                Object.keys(variables[category]).forEach(function(key) {
                    var fullKey = category + '.' + key;
                    // Compose literal double-brace placeholders without triggering Blade
                    var displayText = '{' + '{' + fullKey + '}' + '}';
                    
                    console.log('Creating variable button for:', fullKey, 'with display text:', displayText);
                    
                    var variableButton = $('<button type="button" class="variable-item btn btn-sm btn-outline-primary me-2 mb-1"></button>')
                        .attr('data-variable', fullKey)
                        .attr('data-display-text', displayText)
                        .attr('title', variables[category][key])
                        .text(displayText)
                        .on('click', function() {
                            var variableName = $(this).data('variable');
                            var insertText = $(this).data('display-text');
                            
                            console.log('Variable clicked:', variableName);
                            console.log('Text to insert:', insertText);
                            
                            // Try to insert into CodeMirror editor first
                            if (editor && typeof editor.getCursor === 'function') {
                                try {
                                    var cursor = editor.getCursor();
                                    editor.replaceRange(insertText, cursor);
                                    editor.focus();
                                    console.log('Variable inserted via CodeMirror:', insertText);
                                } catch (error) {
                                    console.error('Error inserting via CodeMirror:', error);
                                    insertIntoTextarea(insertText);
                                }
                            } else {
                                console.log('CodeMirror not available, using textarea insertion');
                                insertIntoTextarea(insertText);
                            }
                            
                            refreshPreview();
                        });
                    
                    console.log('Button created with text:', variableButton.text());
                    variablesRow.append(variableButton);
                });
                
                newContainer.append(variablesRow);
            });
            
            // Replace the entire container
            $('#variables-container').replaceWith(newContainer);
            
            console.log('Variables loaded successfully');
            console.log('Final container HTML:', $('#variables-container').html());
            
            // Helper function to insert into regular textarea
            function insertIntoTextarea(text) {
                var textarea = document.getElementById('content');
                if (textarea) {
                    var start = textarea.selectionStart;
                    var end = textarea.selectionEnd;
                    var value = textarea.value;
                    
                    textarea.value = value.substring(0, start) + text + value.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + text.length;
                    textarea.focus();
                    
                    console.log('Variable inserted via textarea:', text);
                    $(textarea).trigger('input');
                }
            }
            
            // Add click handlers for variables
            $(document).on('click', '.variable-item', function() {
                const variable = $(this).data('variable');
                const variableText = '{' + '{' + variable + '}' + '}';
                
                // Try to insert into CodeMirror editor first
                if (editor && typeof editor.getCursor === 'function') {
                    try {
                        const cursor = editor.getCursor();
                        editor.replaceRange(variableText, cursor);
                        editor.focus();
                        console.log('Variable inserted via CodeMirror:', variableText);
                    } catch (error) {
                        console.error('Error inserting via CodeMirror:', error);
                        // Fallback to textarea
                        insertIntoTextarea(variableText);
                    }
                } else {
                    // Fallback to regular textarea
                    insertIntoTextarea(variableText);
                }
                
                // Trigger preview refresh
                refreshPreview();
            });
            
            // Helper function to insert into regular textarea
            function insertIntoTextarea(text) {
                const textarea = document.getElementById('content');
                if (textarea) {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const value = textarea.value;
                    
                    textarea.value = value.substring(0, start) + text + value.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + text.length;
                    textarea.focus();
                    
                    console.log('Variable inserted via textarea:', text);
                    
                    // Trigger input event for preview
                    $(textarea).trigger('input');
                }
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Failed to load variables:', error);
            $('#variables-container').html('<p class="text-danger">Failed to load variables. Please refresh the page.</p>');
        });
}

function refreshPreview() {
    // Get content from CodeMirror or fallback to textarea
    let content = '';
    if (editor && typeof editor.getValue === 'function') {
        try {
            content = editor.getValue();
        } catch (error) {
            console.error('Error getting content from CodeMirror:', error);
            content = document.getElementById('content').value || '';
        }
    } else {
        content = document.getElementById('content').value || '';
    }
    
    if (!content.trim()) {
        $('#preview-content').html('<p class="text-muted">Enter template content to see preview...</p>');
        return;
    }

    // Simple client-side preview with sample data
    let preview = content;
    
    // Default sample data if not provided from server
    const defaultSampleData = {
        user: {
            name: 'John Doe',
            email: 'john.doe@example.com',
            first_name: 'John',
            last_name: 'Doe'
        },
        company: {
            name: 'Acme Corporation',
            website: 'https://acme.com'
        },
        order: {
            id: '#12345',
            total: '$299.99',
            date: '2024-08-17'
        },
        system: {
            app_name: 'VI Portal',
            support_email: 'support@viportal.com',
            date: new Date().toLocaleDateString()
        }
    };
    
    // Use server-provided sample data or fallback to default
    const previewData = typeof sampleData !== 'undefined' && sampleData ? sampleData : defaultSampleData;
    
    // Replace variables with sample data
    function replaceVariables(text, data, prefix = '') {
        Object.keys(data).forEach(key => {
            const fullKey = prefix ? prefix + '.' + key : key;
            if (typeof data[key] === 'object' && data[key] !== null) {
                text = replaceVariables(text, data[key], fullKey);
            } else {
                const regex = new RegExp('\\{\\{' + fullKey.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\}\\}', 'g');
                text = text.replace(regex, data[key]);
            }
        });
        return text;
    }
    
    preview = replaceVariables(preview, previewData);
    
    // Convert line breaks to HTML
    preview = preview.replace(/\n/g, '<br>');
    
    $('#preview-content').html(
        '<div class="alert alert-info">' +
            '<small><i class="fas fa-info-circle"></i> This is a preview using sample data</small>' +
        '</div>' +
        '<div class="preview-content" style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;">' + preview + '</div>'
    );
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
