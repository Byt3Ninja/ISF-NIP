@extends('layouts.admin')

@section('title', __('notification_triggers.trigger_details'))

@section('content')
<div class="">
  <div class="">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0">{{ __('notification_triggers.trigger_details') }}</h5>
            <div>
              <a href="{{ route('notification-triggers.edit', $notificationTrigger) }}" class="btn btn-primary me-2">
                <i class="ti ti-edit me-2"></i> {{ __('notification_triggers.edit') }}
              </a>
              <a href="{{ route('notification-triggers.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-{{ $l == 'en' ? 'left' : 'right' }} me-2"></i> {{ __('notification_triggers.back_to_list') }}
              </a>
            </div>
          </div>

          <div class="card-body mt-6">
            <div class="row">
              <div class="col-md-8">
                <div class="card">
                  <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('notification_triggers.basic_information') }}</h6>
                  </div>
                  <div class="card-body">
                    <div class="row mb-3">
                      <div class="col-sm-4">
                        <strong>{{ __('notification_triggers.trigger_name') }}:</strong>
                      </div>
                      <div class="col-sm-8">
                        {{ $notificationTrigger->name }}
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-sm-4">
                        <strong>{{ __('notification_triggers.display_title') }}:</strong>
                      </div>
                      <div class="col-sm-8">
                        {{ $notificationTrigger->title ?? '-' }}
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-sm-4">
                        <strong>{{ __('notification_triggers.description') }}:</strong>
                      </div>
                      <div class="col-sm-8">
                        {{ $notificationTrigger->description ?? '-' }}
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-sm-4">
                        <strong>{{ __('notification_triggers.event') }}:</strong>
                      </div>
                      <div class="col-sm-8">
                        <span class="badge bg-label-info">{{ $notificationTrigger->event ?? '-' }}</span>
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-sm-4">
                        <strong>{{ __('notification_triggers.status') }}:</strong>
                      </div>
                      <div class="col-sm-8">
                        @if($notificationTrigger->is_active)
                          <span class="badge bg-label-success">{{ __('notification_triggers.active') }}</span>
                        @else
                          <span class="badge bg-label-secondary">{{ __('notification_triggers.inactive') }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-sm-4">
                        <strong>{{ __('notification_triggers.priority') }}:</strong>
                      </div>
                      <div class="col-sm-8">
                        {{ $notificationTrigger->priority ?? 0 }}
                      </div>
                    </div>
                    <div class="row mb-3">
                      <div class="col-sm-4">
                        <strong>{{ __('notification_triggers.template') }}:</strong>
                      </div>
                      <div class="col-sm-8">
                        @if($notificationTrigger->template)
                          <a href="{{ route('notification-templates.show', $notificationTrigger->template) }}" class="text-primary">
                            {{ $notificationTrigger->template->title }}
                          </a>
                        @else
                          <em class="text-muted">{{ __('notification_triggers.no_template') }}</em>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card mt-3">
                  <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('notification_triggers.conditions') }}</h6>
                  </div>
                  <div class="card-body">
                    @if($notificationTrigger->conditions && count($notificationTrigger->conditions) > 0)
                      <div class="table-responsive">
                        <table class="table table-sm">
                          <thead>
                            <tr>
                              <th>{{ __('notification_triggers.field') }}</th>
                              <th>{{ __('notification_triggers.operator') }}</th>
                              <th>{{ __('notification_triggers.value') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($notificationTrigger->conditions as $condition)
                              <tr>
                                <td><code>{{ $condition['field'] ?? '' }}</code></td>
                                <td><span class="badge bg-label-primary">{{ $condition['operator'] ?? '' }}</span></td>
                                <td><code>{{ $condition['value'] ?? '' }}</code></td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @else
                      <p class="text-muted mb-0">{{ __('notification_triggers.no_conditions') }}</p>
                    @endif
                  </div>
                </div>

                <div class="card mt-3">
                  <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('notification_triggers.recipients') }}</h6>
                  </div>
                  <div class="card-body">
                    @if($notificationTrigger->recipient_rules && count($notificationTrigger->recipient_rules) > 0)
                      <div class="table-responsive">
                        <table class="table table-sm">
                          <thead>
                            <tr>
                              <th>{{ __('notification_triggers.recipient_type') }}</th>
                              <th>{{ __('notification_triggers.value') }}</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($notificationTrigger->recipient_rules as $rule)
                              <tr>
                                <td><span class="badge bg-label-info">{{ $rule['type'] ?? '' }}</span></td>
                                <td><code>{{ $rule['value'] ?? '-' }}</code></td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @else
                      <p class="text-muted mb-0">{{ __('notification_triggers.no_recipients') }}</p>
                    @endif
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="card">
                  <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('notification_triggers.actions') }}</h6>
                  </div>
                  <div class="card-body">
                    <div class="d-grid gap-2">
                      <a href="{{ route('notification-triggers.edit', $notificationTrigger) }}" class="btn btn-label-primary">
                        <i class="ti ti-edit me-2"></i> {{ __('notification_triggers.edit') }}
                      </a>
                      <button type="button" class="btn btn-label-info" onclick="toggleActive({{ $notificationTrigger->id }})">
                        <i class="ti ti-{{ $notificationTrigger->is_active ? 'player-pause' : 'player-play' }} me-2"></i>
                        {{ $notificationTrigger->is_active ? __('notification_triggers.deactivate') : __('notification_triggers.activate') }}
                      </button>
                      <button type="button" class="btn btn-label-success" onclick="testTrigger({{ $notificationTrigger->id }})">
                        <i class="ti ti-send me-2"></i> {{ __('notification_triggers.test') }}
                      </button>
                      <button type="button" class="btn btn-label-warning" onclick="showCloneModal({{ $notificationTrigger->id }}, '{{ $notificationTrigger->name }}')">
                        <i class="ti ti-copy me-2"></i> {{ __('notification_triggers.clone') }}
                      </button>
                    </div>
                  </div>
                </div>

                <div class="card mt-3">
                  <div class="card-header">
                    <h6 class="card-title mb-0">{{ __('notification_triggers.metadata') }}</h6>
                  </div>
                  <div class="card-body">
                    <div class="row mb-2">
                      <div class="col-6">
                        <small class="text-muted">{{ __('notification_triggers.created_by') }}:</small>
                      </div>
                      <div class="col-6">
                        <small>{{ $notificationTrigger->creator->name ?? __('notification_triggers.unknown') }}</small>
                      </div>
                    </div>
                    <div class="row mb-2">
                      <div class="col-6">
                        <small class="text-muted">{{ __('notification_triggers.created_at') }}:</small>
                      </div>
                      <div class="col-6">
                        <small>{{ $notificationTrigger->created_at?->format('Y-m-d H:i') ?? '-' }}</small>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-6">
                        <small class="text-muted">{{ __('notification_triggers.updated_at') }}:</small>
                      </div>
                      <div class="col-6">
                        <small>{{ $notificationTrigger->updated_at?->format('Y-m-d H:i') ?? '-' }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('pagescripts')
<script>
function toggleActive(triggerId) {
    if (confirm('{{ __('notification_triggers.are_you_sure') }}')) {
        fetch(`/admin/notification-triggers/${triggerId}/toggle-active`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __('notification_triggers.unexpected_error') }}');
        });
    }
}

function testTrigger(triggerId) {
    if (confirm('{{ __('notification_triggers.test_confirm') }}')) {
        fetch(`/admin/notification-triggers/${triggerId}/test`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __('notification_triggers.unexpected_error') }}');
        });
    }
}

function showCloneModal(triggerId, triggerName) {
    const newName = prompt('{{ __('notification_triggers.clone_prompt') }}', triggerName + ' (Copy)');
    if (newName) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/notification-triggers/${triggerId}/clone`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const nameField = document.createElement('input');
        nameField.type = 'hidden';
        nameField.name = 'name';
        nameField.value = newName;
        
        form.appendChild(csrfToken);
        form.appendChild(nameField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
