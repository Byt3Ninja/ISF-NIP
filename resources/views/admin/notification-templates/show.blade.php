@extends('layouts.admin')

@section('title', __('notification_templates.view_template'))

@section('content')
<div class="">
  <div class="">
    <div class="card">
      <div class="card-header mb-6 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0 text-capitalize">
            <i class="ti ti-eye me-2"></i>
            {{ __('notification_templates.view_template') }}
          </h5>
          <div class="d-flex align-items-center gap-2">
            <a href="{{ route('notification-templates.edit', $notificationTemplate) }}" class="btn btn-primary">
              <i class="ti ti-edit me-1"></i>
              {{ __('notification_templates.edit') }}
            </a>
            <a href="{{ route('notification-templates.index') }}" class="btn btn-label-secondary">
              <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-1"></i>
              {{ __('notification_templates.back') }}
            </a>
          </div>
        </div>
      </div>
      
      <div class="card-body">
        <!-- Template Information -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card border">
              <div class="card-header">
                <h6 class="card-title mb-0">
                  <i class="ti ti-info-circle me-1"></i>
                  {{ __('notification_templates.template_information') }}
                </h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.name') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    <code>{{ $notificationTemplate->name }}</code>
                    @if($notificationTemplate->parent_template_id)
                      <small class="text-muted d-block">
                        <i class="ti ti-git-branch"></i> {{ __('notification_templates.cloned') }}
                      </small>
                    @endif
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.title') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    {{ $notificationTemplate->title }}
                  </div>
                </div>
                @if($notificationTemplate->description)
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.description') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    {{ $notificationTemplate->description }}
                  </div>
                </div>
                @endif
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.channel') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    <span class="badge bg-label-{{ $notificationTemplate->channel == 'email' ? 'danger' : ($notificationTemplate->channel == 'whatsapp' ? 'success' : 'info') }}">
                      <i class="ti ti-{{ $notificationTemplate->channel == 'email' ? 'mail' : ($notificationTemplate->channel == 'whatsapp' ? 'brand-whatsapp' : 'bell') }} me-1"></i>
                      {{ ucfirst($notificationTemplate->channel) }}
                    </span>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.status') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    <span class="badge bg-label-{{ $notificationTemplate->is_active ? 'success' : 'secondary' }}">
                      <i class="ti ti-circle-dot me-1"></i>
                      {{ $notificationTemplate->is_active ? __('notification_templates.active') : __('notification_templates.inactive') }}
                    </span>
                  </div>
                </div>
                @if($notificationTemplate->sender)
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.sender') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    <code>{{ $notificationTemplate->sender }}</code>
                  </div>
                </div>
                @endif
                @if($notificationTemplate->subject)
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.subject') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    {{ $notificationTemplate->subject }}
                  </div>
                </div>
                @endif
              </div>
            </div>
          </div>
          
          <div class="col-md-6">
            <div class="card border">
              <div class="card-header">
                <h6 class="card-title mb-0">
                  <i class="ti ti-clock me-1"></i>
                  {{ __('notification_templates.metadata') }}
                </h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.version') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    <span class="badge bg-label-info">v{{ $notificationTemplate->version }}</span>
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.created_by') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    {{ $notificationTemplate->creator->first_name_en ?? __('notification_templates.unknown') }}
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.created_at') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    {{ $notificationTemplate->created_at->format('M d, Y H:i') }}
                  </div>
                </div>
                <hr>
                <div class="row">
                  <div class="col-sm-4">
                    <strong>{{ __('notification_templates.updated_at') }}:</strong>
                  </div>
                  <div class="col-sm-8">
                    {{ $notificationTemplate->updated_at->format('M d, Y H:i') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Template Content -->
        <div class="row">
          <div class="col-lg-8">
            <div class="card border">
              <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                  <li class="nav-item">
                    <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#content-tab" role="tab">
                      <i class="ti ti-code me-1"></i>
                      {{ __('notification_templates.template_content') }}
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
                  <div class="tab-pane fade show active" id="content-tab" role="tabpanel">
                    <pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ $notificationTemplate->content }}</code></pre>
                  </div>
                  
                  <div class="tab-pane fade" id="preview-tab" role="tabpanel">
                    <div id="preview-body" class="border rounded p-3" style="min-height: 300px; background: #f8f9fa;">
                      <div class="alert alert-info">
                        <small><i class="ti ti-info-circle"></i> {{ __('notification_templates.preview_sample_data') }}</small>
                      </div>
                      <div class="preview-content p-3">
                        {!! $renderedContent ?? $notificationTemplate->content !!}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
        
        <!-- Actions -->
        <div class="d-flex justify-content-between align-items-center mt-6">
          <a href="{{ route('notification-templates.index') }}" class="btn btn-label-secondary">
            <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-1"></i>
            {{ __('notification_templates.back') }}
          </a>
          
          <div>
            <button type="button" class="btn btn-outline-info me-2" onclick="cloneTemplate({{ $notificationTemplate->id }})">
              <i class="ti ti-copy me-1"></i>
              {{ __('notification_templates.clone') }}
            </button>
            
            <button type="button" class="btn btn-outline-primary me-2" id="test-template-btn">
              <i class="ti ti-send me-1"></i>
              {{ __('notification_templates.test_send') }}
            </button>
            
            <a href="{{ route('notification-templates.edit', $notificationTemplate) }}" class="btn btn-primary">
              <i class="ti ti-edit me-1"></i>
              {{ __('notification_templates.edit') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Clone Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('notification_templates.clone_template') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="clone-form">
        <div class="modal-body">
          <div class="form-group mb-3">
            <label for="clone-name" class="form-label">{{ __('notification_templates.template_name') }}</label>
            <input type="text" class="form-control" id="clone-name" name="name" required>
          </div>
          <div class="form-group mb-3">
            <label for="clone-title" class="form-label">{{ __('notification_templates.template_title_optional') }}</label>
            <input type="text" class="form-control" id="clone-title" name="title">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('notification_templates.cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('notification_templates.clone_template') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadVariables();
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
                    
                    var variableSpan = $('<span class="badge bg-label-primary me-2 mb-1"></span>')
                        .attr('title', variables[category][key])
                        .text(displayText);
                    
                    variablesRow.append(variableSpan);
                });
                
                newContainer.append(variablesRow);
            });
            
            $('#variables-container').replaceWith(newContainer);
        })
        .fail(function() {
            $('#variables-container').html('<p class="text-danger">{{ __("notifications.variables_load_failed") }}</p>');
        });
}

function cloneTemplate(templateId) {
    $('#clone-form')[0].reset();
    new bootstrap.Modal(document.getElementById('cloneModal')).show();
    
    $('#clone-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: $('#clone-name').val(),
            title: $('#clone-title').val()
        };
        
        $.post(`/admin/notification-templates/${templateId}/clone`, formData)
            .done(function(response) {
                bootstrap.Modal.getInstance(document.getElementById('cloneModal')).hide();
                window.location.href = `/admin/notification-templates/${response.id}/edit`;
            })
            .fail(function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorMsg = '';
                    Object.values(xhr.responseJSON.errors).forEach(errors => {
                        errors.forEach(error => errorMsg += error + '\n');
                    });
                    alert(errorMsg);
                } else {
                    alert('{{ __("notifications.clone_failed") }}');
                }
            });
    });
}
</script>
@endsection