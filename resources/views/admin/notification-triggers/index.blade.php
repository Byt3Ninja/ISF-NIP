@extends('layouts.admin')

@section('title', 'Notification Triggers')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Notification Triggers</h3>
                    <a href="{{ route('admin.notification-triggers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Trigger
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="event-filter">
                                <option value="">All Events</option>
                                @foreach($availableEvents as $eventKey => $eventLabel)
                                    <option value="{{ $eventKey }}" {{ request('event') == $eventKey ? 'selected' : '' }}>
                                        {{ $eventLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="status-filter">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="search-filter" placeholder="Search triggers..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-secondary" id="clear-filters">Clear</button>
                        </div>
                    </div>

                    <!-- Triggers Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Event</th>
                                    <th>Template</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($triggers as $trigger)
                                    <tr>
                                        <td>
                                            <strong>{{ $trigger->title }}</strong><br>
                                            <small class="text-muted">{{ $trigger->name }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $availableEvents[$trigger->event] ?? $trigger->event }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.notification-templates.show', $trigger->template) }}" class="text-decoration-none">
                                                {{ $trigger->template->title }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $trigger->priority > 50 ? 'danger' : ($trigger->priority > 25 ? 'warning' : 'secondary') }}">
                                                {{ $trigger->priority }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $trigger->is_active ? 'success' : 'secondary' }}">
                                                {{ $trigger->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                {{ $trigger->created_at->format('M d, Y') }}<br>
                                                by {{ $trigger->creator->name }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.notification-triggers.show', $trigger) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.notification-triggers.edit', $trigger) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-{{ $trigger->is_active ? 'warning' : 'success' }}" 
                                                        onclick="toggleActive({{ $trigger->id }})" title="{{ $trigger->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $trigger->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        onclick="showCloneModal({{ $trigger->id }}, '{{ $trigger->name }}')" title="Clone">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="testTrigger({{ $trigger->id }})" title="Test">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteTrigger({{ $trigger->id }}, '{{ $trigger->name }}')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-bell-slash fa-3x mb-3"></i><br>
                                            No notification triggers found. <a href="{{ route('admin.notification-triggers.create') }}">Create your first trigger</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if(is_object($triggers) && method_exists($triggers, 'hasPages') && $triggers->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $triggers->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clone Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="clone-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Clone Notification Trigger</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="clone-name">New Trigger Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clone-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="clone-title">New Trigger Title</label>
                        <input type="text" class="form-control" id="clone-title" name="title">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Clone Trigger</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Filter functionality
    $('#event-filter, #status-filter').change(function() {
        applyFilters();
    });

    $('#search-filter').on('input', debounce(function() {
        applyFilters();
    }, 500));

    $('#clear-filters').click(function() {
        $('#event-filter, #status-filter').val('');
        $('#search-filter').val('');
        applyFilters();
    });

    function applyFilters() {
        const params = new URLSearchParams();
        
        const event = $('#event-filter').val();
        const status = $('#status-filter').val();
        const search = $('#search-filter').val();
        
        if (event) params.append('event', event);
        if (status !== '') params.append('is_active', status);
        if (search) params.append('search', search);
        
        window.location.search = params.toString();
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});

function toggleActive(triggerId) {
    if (confirm('Are you sure you want to change the status of this trigger?')) {
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
            alert('An error occurred while updating the trigger status.');
        });
    }
}

function showCloneModal(triggerId, triggerName) {
    $('#clone-name').val(triggerName + ' (Copy)');
    $('#clone-title').val('');
    $('#clone-form').attr('action', `/admin/notification-triggers/${triggerId}/clone`);
    $('#cloneModal').modal('show');
}

function testTrigger(triggerId) {
    if (confirm('Are you sure you want to send a test notification using this trigger?')) {
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
            alert('An error occurred while sending the test notification.');
        });
    }
}

function deleteTrigger(triggerId, triggerName) {
    if (confirm(`Are you sure you want to delete the trigger "${triggerName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/notification-triggers/${triggerId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
