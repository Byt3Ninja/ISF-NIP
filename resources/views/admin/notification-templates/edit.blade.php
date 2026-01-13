@extends('layouts.admin')

@section('title', __('notification_templates.edit_template'))

@section('vendorstyle')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/material-darker.min.css">
@endsection

@section('content')
<div class="">
  <div class="">
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    
    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-triangle me-2"></i>
        {{ __('notification_templates.validation_errors') }}
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif
    
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-6">
        <h5 class="card-title mb-0 text-capitalize">
          {{ __('notification_templates.edit_template') }}
        </h5>
        <a href="{{ route('notification-templates.index') }}" class="btn btn-label-secondary">
          <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i>
          {{ __('notification_triggers.back_to_list') }}
        </a>
      </div>
      <div class="card-body">
        <form id="template-form" action="{{ route('notification-templates.update', $notificationTemplate ?? 0) }}" method="POST">
          @csrf
          @method('PUT')
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-4">
                <label for="name" class="form-label">
                  {{ __('notification_templates.template_name') }}
                  <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $notificationTemplate->name ?? '') }}" 
                       required 
                       placeholder="{{ __('notification_templates.name_placeholder') }}">
                <small class="form-text text-muted">
                  {{ __('notification_templates.name_help') }}
                </small>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group mb-4">
                <label for="title" class="form-label">
                  {{ __('notification_templates.template_title') }}
                  <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control @error('title') is-invalid @enderror" 
                       id="title" 
                       name="title" 
                       value="{{ old('title', $notificationTemplate->title ?? '') }}" 
                       required
                       placeholder="{{ __('notification_templates.title_placeholder') }}">
                <small class="form-text text-muted">
                  {{ __('notification_templates.title_help') }}
                </small>
                @error('title')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          
          <div class="form-group mb-4">
            <label for="description" class="form-label">
              {{ __('notification_templates.description') }}
            </label>
            <textarea class="form-control @error('description') is-invalid @enderror" 
                      id="description" 
                      name="description" 
                      rows="3"
                      placeholder="{{ __('notification_templates.description_placeholder') }}">{{ old('description', $notificationTemplate->description ?? '') }}</textarea>
            <small class="form-text text-muted">
              {{ __('notification_templates.description_help') }}
            </small>
            @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group mb-4">
                <label for="channel" class="form-label">
                  {{ __('notification_templates.channel') }}
                  <span class="text-danger">*</span>
                </label>
                <select class="form-select @error('channel') is-invalid @enderror" 
                        id="channel" 
                        name="channel" 
                        required>
                  <option value="">{{ __('notification_templates.select_channel') }}</option>
                  @foreach($channels as $channel)
                    <option value="{{ $channel }}" {{ old('channel', $notificationTemplate->channel ?? '') == $channel ? 'selected' : '' }}>
                      {{ ucfirst($channel) }}
                    </option>
                  @endforeach
                </select>
                <small class="form-text text-muted">
                  {{ __('notification_templates.channel_help') }}
                </small>
                @error('channel')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-group mb-4" id="sender-group" style="display: {{ ($notificationTemplate->channel ?? '') == 'email' || ($notificationTemplate->channel ?? '') == 'whatsapp' ? 'block' : 'none' }};">
                <label for="sender" class="form-label">
                  {{ __('notification_templates.sender') }}
                </label>
                <input type="text" 
                       class="form-control @error('sender') is-invalid @enderror" 
                       id="sender" 
                       name="sender" 
                       value="{{ old('sender', $notificationTemplate->sender ?? '') }}"
                       placeholder="{{ __('notification_templates.sender_placeholder') }}">
                <small class="form-text text-muted">
                  {{ __('notification_templates.sender_help') }}
                </small>
                @error('sender')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          
          <div class="form-group mb-4" id="subject-group" style="display: {{ ($notificationTemplate->channel ?? '') == 'email' ? 'block' : 'none' }};">
            <label for="subject" class="form-label">
              {{ __('notification_templates.subject') }}
              <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control @error('subject') is-invalid @enderror" 
                   id="subject" 
                   name="subject" 
                   value="{{ old('subject', $notificationTemplate->subject ?? '') }}"
                   placeholder="{{ __('notification_templates.subject_placeholder') }}">
            <small class="form-text text-muted">
              {{ __('notification_templates.subject_help') }}
            </small>
            @error('subject')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          
          <div class="row">
            <div class="col-lg-8">
              <div class="card border">
                <div class="card-header">
                  <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                      <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#editor-tab" role="tab">
                        <i class="ti ti-code me-1"></i>
                        {{ __('notification_templates.editor') }}
                      </button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#preview-tab" role="tab">
                        <i class="ti ti-eye me-1"></i>
                        {{ __('notification_templates.preview') }}
                      </button>
                    </li>
                  </ul>
                </div>
                <div class="card-body">
                  <div class="tab-content">
                    <div class="tab-pane fade show active" id="editor-tab" role="tabpanel">
                      <textarea class="form-control @error('content') is-invalid @enderror" 
                                id="content" 
                                name="content" 
                                rows="15"
                                required
                                placeholder="{{ __('notification_templates.content_placeholder') }}">{{ old('content', $notificationTemplate->content ?? '') }}</textarea>
                      @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                    
                    <div class="tab-pane fade" id="preview-tab" role="tabpanel">
                      <div id="preview-body" class="border rounded p-3" style="min-height: 300px; background: #f8f9fa;">
                        <p class="text-muted">{{ __('notification_templates.preview_empty') }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <small class="form-text text-muted mt-2">
                {{ __('notification_templates.content_help') }}
              </small>
            </div>
            
            <div class="col-lg-4">
              <div class="card">
                <div class="card-header">
                  <h6 class="card-title mb-0">
                    <i class="ti ti-tags me-1"></i>
                    {{ __('notification_templates.available_variables') }}
                  </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                  <div id="variables-container">
                    <div class="text-center py-3">
                      <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <p class="text-muted mb-0 mt-2">{{ __('notification_templates.loading_variables') }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="d-flex justify-content-between align-items-center mt-6">            
            <button type="button" class="btn btn-outline-primary me-3" id="test-template-btn">
              <i class="ti ti-send me-1"></i>
              {{ __('notification_templates.test_send') }}
            </button>
            
            <button type="submit" class="btn btn-primary" id="save-template-btn">
              <i class="ti ti-device-floppy me-1"></i>
              {{ __('notification_templates.update_template') }}
            </button>
          </div>
        </form>
      </div>
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
let sampleData = @json($sampleData ?? []);

$(document).ready(function() {
    // Initialize CodeMirror
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
            lineWrapping: true
        });
        
        editor.on('change', function() {
            editor.save();
            refreshPreview();
        });
    } else {
        $('#content').on('input', refreshPreview);
    }
    
    loadVariables();
    
    // Channel change handler
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
    
    // Tab switching
    $('[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('data-bs-target');
        
        if (target === '#editor-tab') {
            if (editor) editor.refresh();
        } else if (target === '#preview-tab') {
            refreshPreview();
        }
    });
});

function loadVariables() {
    $.get('{{ route("notification-templates.variables") }}')
        .done(function(variables) {
            var newContainer = $('<div id="variables-container"></div>');
            
            Object.keys(variables).forEach(function(category) {
                var categoryHeader = $('<h6 class="text-primary mb-2"></h6>').text(category.charAt(0).toUpperCase() + category.slice(1));
                newContainer.append(categoryHeader);
                
                var variablesRow = $('<div class="mb-3"></div>');
                
                Object.keys(variables[category]).forEach(function(key) {
                    var fullKey = category + '.' + key;
                    var displayText = '{' + '{' + fullKey + '}' + '}';
                    
                    var variableButton = $('<button type="button" class="variable-item btn btn-sm btn-outline-primary me-2 mb-1"></button>')
                        .attr('data-variable', fullKey)
                        .attr('title', variables[category][key])
                        .text(displayText)
                        .on('click', function() {
                            var insertText = displayText;
                            
                            if (editor && typeof editor.getCursor === 'function') {
                                try {
                                    var cursor = editor.getCursor();
                                    editor.replaceRange(insertText, cursor);
                                    editor.focus();
                                } catch (error) {
                                    insertIntoTextarea(insertText);
                                }
                            } else {
                                insertIntoTextarea(insertText);
                            }
                            
                            refreshPreview();
                        });
                    
                    variablesRow.append(variableButton);
                });
                
                newContainer.append(variablesRow);
            });
            
            $('#variables-container').replaceWith(newContainer);
            
            function insertIntoTextarea(text) {
                var textarea = document.getElementById('content');
                if (textarea) {
                    var start = textarea.selectionStart;
                    var end = textarea.selectionEnd;
                    var value = textarea.value;
                    
                    textarea.value = value.substring(0, start) + text + value.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + text.length;
                    textarea.focus();
                    
                    $(textarea).trigger('input');
                }
            }
        })
        .fail(function() {
            $('#variables-container').html('<p class="text-danger">{{ __("notifications.variables_load_failed") }}</p>');
        });
}

function refreshPreview() {
    let content = '';
    if (editor && typeof editor.getValue === 'function') {
        try {
            content = editor.getValue();
        } catch (error) {
            content = document.getElementById('content').value || '';
        }
    } else {
        content = document.getElementById('content').value || '';
    }
    
    if (!content.trim()) {
        $('#preview-body').html('<p class="text-muted">{{ __("notifications.preview_empty") }}</p>');
        return;
    }

    let preview = content;
    
    const defaultSampleData = {
        user: { name: 'John Doe', email: 'john.doe@example.com' },
        company: { name: 'Acme Corporation' },
        system: { app_name: 'VI Portal', date: new Date().toLocaleDateString() }
    };
    
    const previewData = sampleData && Object.keys(sampleData).length ? sampleData : defaultSampleData;
    
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
    preview = preview.replace(/\n/g, '<br>');
    
    $('#preview-body').html(
        '<div class="alert alert-info">' +
            '<small><i class="ti ti-info-circle"></i> {{ __("notifications.preview_sample_data") }}</small>' +
        '</div>' +
        '<div class="preview-content p-3">' + preview + '</div>'
    );
}
</script>
@endsection