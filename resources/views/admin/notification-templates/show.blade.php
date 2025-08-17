@extends('layouts.admin')

@section('title', 'View Notification Template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $notificationTemplate->title }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.notification-templates.edit', $notificationTemplate) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('admin.notification-templates.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Templates
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Template Information -->
                        <div class="col-md-6">
                            <h5>Template Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">Name</th>
                                    <td>{{ $notificationTemplate->name }}</td>
                                </tr>
                                <tr>
                                    <th>Title</th>
                                    <td>{{ $notificationTemplate->title }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $notificationTemplate->description ?: 'No description' }}</td>
                                </tr>
                                <tr>
                                    <th>Channel</th>
                                    <td>
                                        <span class="badge badge-{{ $notificationTemplate->channel == 'email' ? 'primary' : ($notificationTemplate->channel == 'whatsapp' ? 'success' : 'info') }}">
                                            {{ ucfirst($notificationTemplate->channel) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($notificationTemplate->subject)
                                <tr>
                                    <th>Subject</th>
                                    <td>{{ $notificationTemplate->subject }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Sender</th>
                                    <td>{{ $notificationTemplate->sender ?: 'Default' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge badge-{{ $notificationTemplate->is_active ? 'success' : 'secondary' }}">
                                            {{ $notificationTemplate->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Version</th>
                                    <td>v{{ $notificationTemplate->version }}</td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>{{ $notificationTemplate->creator->first_name_en ?? 'Unknown' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $notificationTemplate->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $notificationTemplate->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Variables -->
                        <div class="col-md-6">
                            <h5>Available Variables</h5>
                            @if($notificationTemplate->variables && count($notificationTemplate->variables))
                                <div class="alert alert-info">
                                    <p><strong>Variables detected in this template:</strong></p>
                                    @foreach($notificationTemplate->variables as $variable)
                                        <span class="badge badge-light mr-1 mb-1">{{ '{' . '{' }} {{ $variable }} {{ '}' . '}' }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    No variables detected in this template.
                                </div>
                            @endif

                            @if($notificationTemplate->parentTemplate)
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-code-branch"></i> Template History</h6>
                                    <p>This template is based on: <strong>{{ $notificationTemplate->parentTemplate->name }}</strong></p>
                                </div>
                            @endif

                            @if($notificationTemplate->childTemplates->count() > 0)
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-sitemap"></i> Child Templates</h6>
                                    <p>This template has {{ $notificationTemplate->childTemplates->count() }} child version(s):</p>
                                    <ul class="list-unstyled">
                                        @foreach($notificationTemplate->childTemplates as $child)
                                            <li>
                                                <a href="{{ route('admin.notification-templates.show', $child) }}">
                                                    {{ $child->name }} (v{{ $child->version }})
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Template Content -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Template Content</h5>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="bg-light p-3" style="white-space: pre-wrap;">{{ $notificationTemplate->content }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Preview with Sample Data</h5>
                                    <button type="button" class="btn btn-sm btn-info" id="refresh-preview">
                                        <i class="fas fa-sync"></i> Refresh Preview
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div id="preview-content" class="border p-3 bg-light">
                                        <p class="text-muted">Loading preview...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="button" class="btn btn-success" id="test-send">
                        <i class="fas fa-paper-plane"></i> Test Send
                    </button>
                    <button type="button" class="btn btn-info" onclick="cloneTemplate({{ $notificationTemplate->id }})">
                        <i class="fas fa-copy"></i> Clone Template
                    </button>
                    <a href="{{ route('admin.notification-templates.edit', $notificationTemplate) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Template
                    </a>
                </div>
            </div>
        </div>
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
let templateId = {{ $notificationTemplate->id }};
let templateChannel = '{{ $notificationTemplate->channel }}';

$(document).ready(function() {
    // Load initial preview
    refreshPreview();

    // Preview refresh
    $('#refresh-preview').click(function() {
        refreshPreview();
    });

    // Test send functionality
    $('#test-send').click(function() {
        updateTestSendModal(templateChannel);
        $('#testSendModal').modal('show');
    });

    $('#test-send-form').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            test_email: $('#test-email').val(),
            test_phone: $('#test-phone').val()
        };
        
        $.post(`/admin/notification-templates/${templateId}/test-send`, formData)
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

function refreshPreview() {
    $('#preview-content').html('<p class="text-muted">Loading preview...</p>');
    
    $.get(`/admin/notification-templates/${templateId}/preview`)
        .done(function(response) {
            $('#preview-content').html(`
                <div class="alert alert-success mb-3">
                    <small><i class="fas fa-check-circle"></i> Preview rendered with sample data</small>
                </div>
                <div class="preview-content">${response.rendered_content.replace(/\n/g, '<br>')}</div>
            `);
        })
        .fail(function() {
            $('#preview-content').html('<p class="text-danger">Failed to load preview</p>');
        });
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
</script>
@endsection
