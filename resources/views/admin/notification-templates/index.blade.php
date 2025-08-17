@extends('layouts.admin')

@section('title', 'Notification Templates')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Notification Templates</h3>
                    <a href="{{ route('admin.notification-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Template
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-control" id="channel-filter">
                                <option value="">All Channels</option>
                                <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>Email</option>
                                <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="pusher" {{ request('channel') == 'pusher' ? 'selected' : '' }}>Push</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="status-filter">
                                <option value="">All Status</option>
                                <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="search-input" 
                                       placeholder="Search templates..." value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="search-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Templates Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Title</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Version</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $template)
                                <tr>
                                    <td>
                                        <strong>{{ $template->name }}</strong>
                                        @if($template->parent_template_id)
                                            <small class="text-muted d-block">
                                                <i class="fas fa-code-branch"></i> Cloned
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $template->title }}</td>
                                    <td>
                                        <span class="badge badge-{{ $template->channel == 'email' ? 'primary' : ($template->channel == 'whatsapp' ? 'success' : 'info') }}">
                                            {{ ucfirst($template->channel) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">
                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>v{{ $template->version }}</td>
                                    <td>{{ $template->creator->first_name_en ?? 'Unknown' }}</td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.notification-templates.show', $template) }}" 
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.notification-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                    onclick="previewTemplate({{ $template->id }})" title="Preview">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="cloneTemplate({{ $template->id }})" title="Clone">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteTemplate({{ $template->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No templates found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $templates->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="preview-content"></div>
            </div>
        </div>
    </div>
</div>

<!-- Clone Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clone Template</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="clone-form">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="clone-name">Template Name</label>
                        <input type="text" class="form-control" id="clone-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="clone-title">Template Title (optional)</label>
                        <input type="text" class="form-control" id="clone-title" name="title">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Clone Template</button>
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
    $('#channel-filter, #status-filter').change(function() {
        applyFilters();
    });

    $('#search-btn').click(function() {
        applyFilters();
    });

    $('#search-input').keypress(function(e) {
        if (e.which == 13) {
            applyFilters();
        }
    });

    function applyFilters() {
        const params = new URLSearchParams();
        
        const channel = $('#channel-filter').val();
        const status = $('#status-filter').val();
        const search = $('#search-input').val();
        
        if (channel) params.append('channel', channel);
        if (status) params.append('active', status);
        if (search) params.append('search', search);
        
        window.location.href = '{{ route("admin.notification-templates.index") }}?' + params.toString();
    }
});

function previewTemplate(templateId) {
    $.get(`/admin/notification-templates/${templateId}/preview`)
        .done(function(response) {
            $('#preview-content').html(`
                <div class="card">
                    <div class="card-header">
                        <h6>Rendered Content</h6>
                    </div>
                    <div class="card-body">
                        <div class="rendered-content" style="border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                            ${response.rendered_content.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6>Sample Data Used:</h6>
                    <pre class="bg-light p-2">${JSON.stringify(response.sample_data, null, 2)}</pre>
                </div>
            `);
            $('#previewModal').modal('show');
        })
        .fail(function() {
            alert('Failed to load preview');
        });
}

function cloneTemplate(templateId) {
    $('#clone-form')[0].reset();
    $('#cloneModal').modal('show');
    
    $('#clone-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: $('#clone-name').val(),
            title: $('#clone-title').val()
        };
        
        $.post(`/admin/notification-templates/${templateId}/clone`, formData)
            .done(function(response) {
                $('#cloneModal').modal('hide');
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
                    alert('Failed to clone template');
                }
            });
    });
}

function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template?')) {
        $.ajax({
            url: `/admin/notification-templates/${templateId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function() {
            location.reload();
        })
        .fail(function() {
            alert('Failed to delete template');
        });
    }
}
</script>
@endsection
